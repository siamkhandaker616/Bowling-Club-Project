<x-app-layout>
    <x-slot name="fullWidth"></x-slot>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:var(--font-header);font-size:1.2rem;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin:0;">Waiting Queue</h2>

        </div>
    </x-slot>


    <div class="mod-grid" style="min-height:calc(100vh - 200px);">

        @include('sim.partials.module-dock')

        <div style="padding:0 1rem;max-width:900px;margin:0 auto;">

        

        <div style="display:flex;flex-direction:column;gap:10px;">
            @forelse ($entries as $entry)
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:var(--navy);color:var(--pin-white);">Pos {{ $entry->position }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">{{ $entry->date->format('M j, Y') }}</span>
                        <span style="font-family:var(--font-sub);font-size:0.75rem;">Lane {{ $entry->booking?->lane?->lane_number ?? '—' }} · {{ \App\Helpers\Label::timeSlot($entry->time_slot) }}</span>
                        <span style="font-family:var(--font-mono);font-size:0.55rem;padding:2px 8px;border-radius:50px;text-transform:uppercase;background:{{ $entry->status === 'waiting' ? 'var(--gold-light)' : 'var(--sky)' }};color:var(--navy);border:1px solid var(--navy);">{{ \App\Helpers\Label::queueStatus($entry->status) }}</span>
                    </div>
                </div>
            @empty
                <div style="background:var(--sky-light);border:2px solid var(--navy);border-radius:12px;padding:1rem;text-align:center;">
                    <span style="font-family:var(--font-mono);font-size:0.65rem;color:var(--slate);">You're not in any waiting queue.</span>
                </div>
            @endforelse
        </div>

    </div>
    </div>

    <x-toast />

    @include('sim.partials.responsive')
</x-app-layout>
