<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Показать форму входа в систему.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Обработать попытку входа пользователя с защитой от перебора (throttling).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Укажите адрес электронной почты.',
            'email.email' => 'Введите корректный email адрес.',
            'password.required' => 'Введите пароль.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Слишком много неудачных попыток входа. Пожалуйста, повторите попытку через {$seconds} сек.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('tasks.index'))->with('success', 'С возвращением, '.Auth::user()->name.'!');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Неверный адрес электронной почты или пароль.',
        ])->onlyInput('email');
    }

    /**
     * Показать форму регистрации.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Обработать регистрацию нового пользователя с автологином.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Укажите ваше имя.',
            'email.required' => 'Укажите адрес электронной почты.',
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tasks.index')->with('success', 'Регистрация прошла успешно! Добро пожаловать в TemplateTest.');
    }

    /**
     * Выход из системы с инвалидацией сессии и сбросом CSRF-токена.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Вы успешно вышли из системы.');
    }
}
