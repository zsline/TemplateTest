<x-guest-layout title="Создать аккаунт" subtitle="Начните управлять задачами эффективно и быстро">
    <form action="{{ route('register') }}" method="POST">
        @csrf

        <x-ui.input
            name="name"
            type="text"
            label="Имя"
            placeholder="Алексей Смирнов"
            required
            autofocus
        />

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            placeholder="developer@example.com"
            required
        />

        <x-ui.input
            name="password"
            type="password"
            label="Пароль"
            placeholder="Минимум 8 символов"
            required
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            label="Подтверждение пароля"
            placeholder="Повторите пароль"
            required
        />

        <x-ui.button type="submit" variant="primary" class="btn-block" style="margin-top: 8px;">
            Зарегистрироваться
        </x-ui.button>
    </form>

    <div class="auth-footer">
        <span>Уже есть аккаунт?</span>
        <a href="{{ route('login') }}">Войти</a>
    </div>
</x-guest-layout>
