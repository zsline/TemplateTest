@props([
    'type' => 'status',
    'variant' => 'todo',
    'dot' => true,
])

@php
    $class = 'badge badge-' . $type . '-' . $variant;
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    @if ($dot)
        <span class="badge-dot"></span>
    @endif
    {{ $slot }}
</span>
