<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">My Bookings</h2>
            <span class="badge-role member">Visitor</span>
        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:0 1rem;max-width:900px;margin:0 auto;">

        

        <div style="display:flex;flex-direction:column;gap:10px;">
            @forelse ($bookings as $booking)
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--navy);color:var(--pin-white);">{{ $booking->time_slot }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">{{ $booking->date->format('M j, Y') }}</span>
                        <span style="font-family:var(--font-sub);font-size:0.75rem;">Lane {{ $booking->lane?->lane_number ?? '—' }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ match($booking->status) { 'confirmed' => 'var(--sky)', 'completed' => 'var(--mist)', 'pending' => 'var(--gold-light)', default => 'var(--coral-light)' } }};color:var(--navy);border:1px solid var(--navy);">{{ $booking->status }}</span>
                        @if ($booking->queue_position)
                            <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--gold-light);color:var(--gold-dust);border:1px solid var(--gold);">Queue #{{ $booking->queue_position }}</span>
                        @endif
                    </div>
                    @if (in_array($booking->status, ['pending', 'confirmed']))
                        <form method="POST" action="{{ route('visitor.bookings.cancel', $booking) }}">
                            @csrf
                            <button type="submit" class="btn-lane secondary" style="font-size:0.55rem;padding:5px 12px;">Cancel</button>
                        </form>
                    @endif
                </div>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You have no bookings yet. <a href="{{ route('visitor.bookings.create') }}" style="color:var(--sky-dark);">Book a lane</a>.</span>
                </div>
            @endforelse
        </div>

    </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
