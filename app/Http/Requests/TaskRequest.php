<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Авторизация запроса: проверяет права на редактирование задачи при update.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        if ($task instanceof Task) {
            return (int) $this->user()?->id === (int) $task->user_id;
        }

        return true;
    }

    /**
     * Правила валидации для задачи.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'title' => ['required', 'string', 'min:1', 'max:200'],
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['backlog', 'todo', 'in_progress', 'done'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'due_date' => ['nullable', 'date'],
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
            'title.required' => 'Укажите название задачи.',
            'title.max' => 'Название задачи не должно превышать 200 символов.',
            'project_id.required' => 'Выберите проект для задачи.',
            'project_id.exists' => 'Выбранный проект не существует или не принадлежит вам.',
            'description.max' => 'Описание задачи не должно превышать 5000 символов.',
            'status.required' => 'Укажите статус задачи.',
            'status.in' => 'Указан недопустимый статус задачи.',
            'priority.required' => 'Укажите приоритет задачи.',
            'priority.in' => 'Указан недопустимый приоритет задачи.',
            'due_date.date' => 'Дедлайн должен быть корректной датой.',
        ];
    }
}
