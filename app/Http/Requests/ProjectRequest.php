<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Авторизация проверяется через политики в контроллере.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации для проекта.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
        ];
    }

    /**
     * Сообщения об ошибках валидации на русском языке.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название проекта.',
            'name.max' => 'Название проекта не должно превышать 150 символов.',
            'description.max' => 'Описание проекта не должно превышать 2000 символов.',
            'color.required' => 'Выберите цвет проекта.',
            'color.regex' => 'Цвет проекта должен быть корректным HEX-кодом (например, #3b82f6).',
        ];
    }
}
