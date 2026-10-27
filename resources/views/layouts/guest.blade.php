<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TemplateTest - Личная эффективность' }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-page">
    <div class="auth-card">
        <div class="auth-header">
            <a href="{{ url('/') }}" class="auth-brand-emblem" title="TemplateTest">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </a>
            @isset($title)
                <h1 class="auth-title">{{ $title }}</h1>
            @endisset
            @isset($subtitle)
                <p class="auth-subtitle">{{ $subtitle }}</p>
            @endisset
        </div>

        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" style="margin-bottom: 20px;" />
        @endif

        @if (session('error'))
            <x-ui.alert type="error" :message="session('error')" style="margin-bottom: 20px;" />
        @endif

        {{ $slot }}
    </div>
</body>
</html>
