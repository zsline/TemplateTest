<x-guest-layout title="Вход в аккаунт" subtitle="Введите ваши данные для доступа к задачам">
    <form action="{{ route('login') }}" method="POST">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            placeholder="developer@example.com"
            required
            autofocus
        />

        <x-ui.input
            name="password"
            type="password"
            label="Пароль"
            placeholder="••••••••"
            required
        />

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <label class="form-checkbox-group">
                <input type="checkbox" name="remember" class="form-checkbox" {{ old('remember') ? 'checked' : '' }}>
                <span class="text-secondary" style="font-size: 13px;">Запомнить меня</span>
            </label>
        </div>

        <x-ui.button type="submit" variant="primary" class="btn-block">
            Войти в аккаунт
        </x-ui.button>
    </form>

    <div class="auth-footer">
        <span>Нет аккаунта?</span>
        <a href="{{ route('register') }}">Зарегистрироваться</a>
    </div>
</x-guest-layout>
