@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($headerActions))
        <div class="card-header">
            @if ($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @isset($headerActions)
                <div>{{ $headerActions }}</div>
            @endisset
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
