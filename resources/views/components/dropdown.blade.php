@props(['align' => 'right', 'width' => '48', 'contentClasses' => ''])

@php
$widthClass = match ($width) {
    '48' => 'width:12rem;',
    default => "width:{$width}rem;",
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $widthClass }} {{ $alignmentClasses }}"
            style="{{ $widthClass }}display:none;box-shadow:0 8px 24px rgba(26,42,58,0.2);border-radius:10px;overflow:hidden;"
            @click="open = false">
        <div style="background:var(--pin-white);border:2px solid var(--navy);border-radius:10px;overflow:hidden;">
            {{ $content }}
        </div>
    </div>
</div>
