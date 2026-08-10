<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class SocialiteController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['google' => 'Google authentication failed. Please try again.']);
        }
    }
}
