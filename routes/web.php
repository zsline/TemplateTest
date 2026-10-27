<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Главная страница: перенаправление авторизованных пользователей в задачи, гостей — на форму входа
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('tasks.index');
    }

    return redirect()->route('login');
});

// Гостевые маршруты (аутентификация и регистрация)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Защищённые маршруты (требуется авторизация)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Быстрое обновление статуса задачи
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Управление задачами (CRUD)
    Route::resource('tasks', TaskController::class);

    // Управление проектами (CRUD)
    Route::resource('projects', ProjectController::class);
});
