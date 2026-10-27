@props([    'type' => 'button',    'variant' => 'primary',    'size' => 'md',    'href' => null,    'class' => '',
])

@php
    $classes = 'btn btn-' . $variant . ($size === 'sm' ? ' btn-sm' : '') . ($class ? ' ' . $class : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif