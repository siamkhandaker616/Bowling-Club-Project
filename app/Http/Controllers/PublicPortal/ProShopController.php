<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use App\Mail\OrderReceipt;
use App\Models\CartItem;
use App\Models\Club;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Services\Payments\SslCommerzGateway;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProShopController extends Controller
{
    public function __construct(private SslCommerzGateway $gateway)
    {
    }

    public function index(Request $request): View
    {
        if (! $this->isOpen()) {
            return view('portal.proshop.coming');
        }

        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('portal.proshop.index', [
            'products' => $products,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function cart(Request $request): View
    {
        if (! $this->isOpen()) {
            return view('portal.proshop.coming');
        }

        $cart = $this->cartItems($request);

        return view('portal.proshop.cart', [
            'cart' => $cart,
            'total' => $cart->sum(fn (CartItem $item) => (float) ($item->product?->price ?? 0) * $item->quantity),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        if (! $this->isOpen()) {
            return response()->json(['error' => 'The Pro Shop is opening soon.'], 422);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::whereKey($validated['product_id'])->where('is_active', true)->first();

        if (! $product) {
            return response()->json(['error' => 'That item is not on the shelf right now.'], 422);
        }

        $sessionId = $request->session()->getId();

        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();

        $wanted = ($cartItem->quantity ?? 0) + $validated['quantity'];

        if ($product->stock < $wanted) {
            return response()->json(['error' => "Only {$product->stock} left in stock."], 422);
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $wanted]);
        } else {
            CartItem::create([
                'session_id' => $sessionId,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "{$product->name} is in your bag.",
            'cart_count' => $this->cartCount($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! $this->isOpen()) {
            return redirect()->route('public.proshop.index');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $cartItem = CartItem::where('session_id', $request->session()->getId())
            ->where('product_id', $validated['product_id'])
            ->first();

        if (! $cartItem) {
            return redirect()->route('public.proshop.cart');
        }

        if ($validated['quantity'] === 0) {
            $cartItem->delete();
        } else {
            $stock = (int) ($cartItem->product?->stock ?? 0);
            $cartItem->update(['quantity' => min($validated['quantity'], $stock)]);
        }

        return redirect()->route('public.proshop.cart');
    }

    public function remove(Request $request): RedirectResponse
    {
        if ($this->isOpen()) {
            $validated = $request->validate([
                'product_id' => ['required', 'integer'],
            ]);

            CartItem::where('session_id', $request->session()->getId())
                ->where('product_id', $validated['product_id'])
                ->delete();
        }

        return redirect()->route('public.proshop.cart');
    }

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        if (! $this->isOpen()) {
            return $this->checkoutError($request, 'The Pro Shop is opening soon.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $cart = $this->cartItems($request);

        if ($cart->isEmpty()) {
            return $this->checkoutError($request, 'Your bag is empty — nothing to check out yet.');
        }

        foreach ($cart as $item) {
            if (! $item->product || $item->product->stock < $item->quantity) {
                return $this->checkoutError(
                    $request,
                    "{$item->product?->name} only has {$item->product?->stock} left — adjust your bag."
                );
            }
        }

        $total = $cart->sum(fn (CartItem $item) => (float) ($item->product?->price ?? 0) * $item->quantity);

        if ($total <= 0) {
            return $this->checkoutError($request, 'Your bag total is zero — nothing to pay.');
        }

        $order = DB::transaction(function () use ($cart, $total) {
            $order = ProductOrder::create();

            foreach ($cart as $item) {
                OrderItem::create([
                    'product_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price,
                ]);
            }

            return $order;
        });

        $payment = $order->payment()->create([
            'transaction_id' => $this->gateway->generateTransactionId(),
            'amount' => $total,
            'currency' => 'BDT',
            'status' => 'pending',
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
        ]);

        if (! $this->gateway->isConfigured()) {
            $order->fulfill();
            $this->clearCart($request);
            $payment->markSuccessful($payment->transaction_id);
            Mail::to($payment->customer_email)->send(new OrderReceipt($order));

            return $this->checkoutResponse($request, $payment);
        }

        try {
            $response = $this->gateway->initSession([
                'total_amount' => (string) $total,
                'currency' => 'BDT',
                'tran_id' => $payment->transaction_id,
                'success_url' => route('public.pay.success', $payment),
                'fail_url' => route('public.pay.fail', $payment),
                'cancel_url' => route('public.pay.cancel', $payment),
                'ipn_url' => route('public.pay.ipn'),
                'cus_name' => $data['customer_name'],
                'cus_email' => $data['customer_email'],
                'cus_phone' => $data['customer_phone'] ?? '',
                'product_name' => 'Pro Shop order #' . $order->id,
                'product_category' => 'Pro Shop',
                'product_profile' => 'general',
            ]);
        } catch (\Throwable $e) {
            Log::warning('SSLCommerz session creation failed: '.$e->getMessage());

            $payment->update([
                'status' => 'failed',
                'error_message' => 'The payment gateway could not be reached. Please try again later.',
            ]);

            return $this->checkoutError($request, 'The payment gateway could not be reached. Please try again later.');
        }

        if (($response['status'] ?? '') === 'SUCCESS') {
            $payment->update([
                'status' => 'processing',
                'session_key' => $response['sessionkey'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $response['GatewayPageURL'],
                ]);
            }

            return redirect()->away($response['GatewayPageURL']);
        }

        $payment->update([
            'status' => 'failed',
            'error_message' => $response['failedreason'] ?? 'The payment gateway rejected the request.',
        ]);

        return $this->checkoutError($request, $payment->error_message);
    }

    private function isOpen(): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('products')) {
            return false;
        }

        $club = Club::first();

        return $club ? (bool) $club->pro_shop_open : true;
    }

    private function cartItems(Request $request): Collection
    {
        return CartItem::where('session_id', $request->session()->getId())
            ->with('product')
            ->get();
    }

    private function cartCount(Request $request): int
    {
        return (int) CartItem::where('session_id', $request->session()->getId())->sum('quantity');
    }

    private function clearCart(Request $request): void
    {
        CartItem::where('session_id', $request->session()->getId())->delete();
    }

    private function checkoutResponse(Request $request, Payment $payment): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('public.pay.success', $payment),
            ]);
        }

        return redirect()->route('public.pay.success', $payment);
    }

    private function checkoutError(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => $message], 422);
        }

        session()->flash('error', $message);

        return redirect()->route('public.proshop.cart');
    }
}
