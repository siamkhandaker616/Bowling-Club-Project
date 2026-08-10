@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1']) }} style="font-size:0.8rem;color:var(--coral);margin-top:4px;">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
