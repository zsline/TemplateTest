<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Атрибуты, доступные для массового заполнения.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    /**
     * Преобразования типов атрибутов.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Обработка жизненного цикла модели:
     * - при переходе в 'done' устанавливается completed_at;
     * - при переходе из 'done' в другой статус completed_at сбрасывается;
     * - если статус остаётся 'done', первоначальное время завершения сохраняется.
     */
    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->isDirty('status')) {
                if ($task->status === 'done') {
                    $task->completed_at = $task->completed_at ?? now();
                } else {
                    $task->completed_at = null;
                }
            } elseif ($task->status === 'done' && $task->completed_at === null) {
                $task->completed_at = now();
            }
        });
    }

    /**
     * Пользователь — создатель и владелец задачи.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проект, к которому привязана задача.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Проверяет, просрочена ли задача:
     * дедлайн раньше сегодняшней даты и задача не завершена.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'done' || $this->due_date === null) {
            return false;
        }

        return $this->due_date->lt(today());
    }

    /**
     * Проверяет, наступил ли дедлайн сегодня и задача не завершена.
     */
    public function isDueToday(): bool
    {
        if ($this->status === 'done' || $this->due_date === null) {
            return false;
        }

        return $this->due_date->isToday();
    }
}
