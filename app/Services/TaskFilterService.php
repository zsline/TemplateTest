<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TaskFilterService
{
    /**
     * Применить набор фильтров и параметров сортировки к запросу задач.
     *
     * @param  Builder  $query  Исходный запрос (уже ограниченный текущим пользователем)
     * @param  array<string, mixed>  $filters  Параметры фильтрации и сортировки
     */
    public function apply(Builder $query, array $filters): Builder
    {
        // 1. Фильтрация по проекту (project_id)
        if (! empty($filters['project_id']) && is_numeric($filters['project_id'])) {
            $query->where('project_id', (int) $filters['project_id']);
        }

        // 2. Фильтрация по статусу (status)
        if (! empty($filters['status']) && is_string($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'active') {
                $query->where('status', '!=', 'done');
            } elseif (in_array($status, ['backlog', 'todo', 'in_progress', 'done'], true)) {
                $query->where('status', $status);
            }
        }

        // 3. Фильтрация по приоритету (priority)
        if (! empty($filters['priority']) && is_string($filters['priority'])) {
            $priority = $filters['priority'];
            if (in_array($priority, ['low', 'medium', 'high', 'critical'], true)) {
                $query->where('priority', $priority);
            }
        }

        // 4. Фильтрация по срокам / дедлайну (deadline или due_date)
        $deadline = $filters['deadline'] ?? $filters['due_date'] ?? null;
        if (! empty($deadline) && is_string($deadline)) {
            $today = Carbon::today()->toDateString();

            switch ($deadline) {
                case 'overdue':
                    $query->whereNotNull('due_date')
                        ->whereDate('due_date', '<', $today)
                        ->where('status', '!=', 'done');
                    break;

                case 'today':
                    // Согласно ТЗ: due_date = today без ограничения по статусу
                    $query->whereDate('due_date', '=', $today);
                    break;

                case 'upcoming':
                    $query->whereNotNull('due_date')
                        ->whereDate('due_date', '>', $today);
                    break;

                case 'no_due_date':
                    $query->whereNull('due_date');
                    break;
            }
        }

        // 5. Безопасная сортировка (sort)
        $sort = $filters['sort'] ?? null;
        switch ($sort) {
            case 'due_date_asc':
                $query->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('id', 'desc');
                break;

            case 'due_date_desc':
                $query->orderByRaw('due_date IS NULL, due_date DESC')->orderBy('id', 'desc');
                break;

            case 'priority_desc':
                // Критический -> Высокий -> Средний -> Низкий (ANSI SQL CASE для совместимости с MySQL и SQLite)
                $query->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                    ->orderBy('id', 'desc');
                break;

            case 'priority_asc':
                // Низкий -> Средний -> Высокий -> Критический
                $query->orderByRaw("CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 WHEN 'critical' THEN 4 ELSE 5 END")
                    ->orderBy('id', 'desc');
                break;

            case 'created_at_asc':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;

            case 'title_asc':
                $query->orderBy('title', 'asc')->orderBy('id', 'desc');
                break;

            case 'title_desc':
                $query->orderBy('title', 'desc')->orderBy('id', 'desc');
                break;

            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                break;
        }

        return $query;
    }
}
