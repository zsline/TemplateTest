<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TemplateTest' }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <div class="header-brand-group">
                <a href="{{ route('tasks.index') }}" class="brand-logo">
                    <div class="brand-emblem">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <span>TemplateTest</span>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="header-nav">
                    <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Задачи</span>
                    </a>
                    <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                        <span>Проекты</span>
                    </a>
                </nav>
            </div>

            <!-- Header User & Controls Group -->
            <div class="header-user-group">
                @auth
                    <div class="user-profile-badge">
                        <div class="user-avatar-circle" title="{{ auth()->user()->name }}">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="user-display-name">{{ auth()->user()->name }}</span>
                    </div>

                    <!-- Desktop Logout Form -->
                    <form action="{{ route('logout') }}" method="POST" class="desktop-logout-form" style="margin: 0;">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" size="sm" title="Выйти из системы">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 15px; height: 15px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span>Выйти</span>
                        </x-ui.button>
                    </form>

                    <!-- Mobile Menu Hamburger Button -->
                    <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Открыть мобильное меню" aria-expanded="false">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                @else
                    <div class="desktop-auth-links">
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Войти</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Регистрация</a>
                    </div>
                    <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Открыть мобильное меню" aria-expanded="false">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                @endauth
            </div>
        </div>

        <!-- Mobile Menu Backdrop -->
        <div class="mobile-menu-backdrop" id="mobileMenuBackdrop"></div>

        <!-- Mobile Menu Drawer / Dropdown Panel -->
        <div class="mobile-menu-drawer" id="mobileMenuDrawer" aria-hidden="true">
            <div class="mobile-menu-inner">
                @auth
                    <!-- Mobile User Information Card -->
                    <div class="mobile-user-card">
                        <div class="user-avatar-circle mobile-avatar">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="mobile-user-details">
                            <span class="mobile-user-name">{{ auth()->user()->name }}</span>
                            <span class="mobile-user-email">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                @endauth

                <!-- Mobile Navigation List -->
                <nav class="mobile-nav-list">
                    <a href="{{ route('tasks.index') }}" class="mobile-nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                        <span class="mobile-nav-item-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="mobile-nav-item-text">Задачи</span>
                        <span class="mobile-nav-item-arrow">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ route('projects.index') }}" class="mobile-nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <span class="mobile-nav-item-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                        </span>
                        <span class="mobile-nav-item-text">Проекты</span>
                        <span class="mobile-nav-item-arrow">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </a>
                </nav>

                <div class="mobile-menu-divider"></div>

                <!-- Mobile Actions (Logout / Auth) -->
                <div class="mobile-menu-actions">
                    @auth
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0; width: 100%;">
                            @csrf
                            <button type="submit" class="mobile-logout-btn">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span>Выйти из аккаунта</span>
                            </button>
                        </form>
                    @else
                        <div style="display: flex; gap: 10px; width: 100%;">
                            <a href="{{ route('login') }}" class="btn btn-secondary btn-block" style="flex: 1; justify-content: center;">Войти</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-block" style="flex: 1; justify-content: center;">Регистрация</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="alerts-container">
            @if (session('success'))
                <x-ui.alert type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-ui.alert type="error" :message="session('error')" />
            @endif
        </div>

        {{ $slot }}
    </main>

    <!-- Mobile Menu Interactive Controller -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const drawer = document.getElementById('mobileMenuDrawer');
            const backdrop = document.getElementById('mobileMenuBackdrop');

            if (!toggleBtn || !drawer || !backdrop) return;

            function openMenu() {
                drawer.classList.add('is-open');
                backdrop.classList.add('is-open');
                toggleBtn.classList.add('is-active');
                toggleBtn.setAttribute('aria-expanded', 'true');
                drawer.setAttribute('aria-hidden', 'false');
                document.body.classList.add('menu-open');
            }

            function closeMenu() {
                drawer.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                toggleBtn.classList.remove('is-active');
                toggleBtn.setAttribute('aria-expanded', 'false');
                drawer.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('menu-open');
            }

            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (drawer.classList.contains('is-open')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            backdrop.addEventListener('click', closeMenu);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                    closeMenu();
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 768 && drawer.classList.contains('is-open')) {
                    closeMenu();
                }
            });
        });
    </script>
</body>
</html>
