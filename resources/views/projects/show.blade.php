<x-app-layout title="{{ $project->name }} - TemplateTest">
    <div class="breadcrumbs">
        <a href="{{ route('projects.index') }}" class="breadcrumb-link">Проекты</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $project->name }}</span>
    </div>

    <!-- Project Hero Header -->
    <div class="project-hero-card">
        <div class="project-hero-header">
            <div class="project-hero-title-group">
                <span class="project-hero-badge" style="background-color: {{ $project->color }};"></span>
                <h1 style="font-size: 26px; font-weight: 700; color: var(--color-text-primary);">
                    {{ $project->name }}
                </h1>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <x-ui.button href="{{ route('projects.edit', $project) }}" variant="secondary">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    <span>Редактировать</span>
                </x-ui.button>

                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить проект «{{ addslashes($project->name) }}»? Это действие необратимо.');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        <span>Удалить</span>
                    </x-ui.button>
                </form>
            </div>
        </div>

        <p class="project-hero-desc">
            {{ $project->description ?: 'Описание проекта не указано. Вы можете добавить его через форму редактирования.' }}
        </p>
    </div>

    <!-- Project Tasks Section -->
    <div style="margin-top: 32px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <div>
                <h2>Задачи проекта ({{ $project->tasks->count() }})</h2>
                <p class="text-secondary" style="font-size: 13px;">Список привязанных к проекту задач и их статусы</p>
            </div>
            <div>
                <x-ui.button href="{{ route('tasks.create', ['project_id' => $project->id]) }}" variant="primary" size="sm">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px; height: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Новая задача</span>
                </x-ui.button>
            </div>
        </div>

        @if ($project->tasks->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="empty-title">В этом проекте пока нет задач</h3>
                <p class="empty-text">Создайте первую задачу для проекта «{{ $project->name }}», чтобы начать работу.</p>
                <x-ui.button href="{{ route('tasks.create', ['project_id' => $project->id]) }}" variant="primary" style="margin-top: 10px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Добавить задачу</span>
                </x-ui.button>
            </div>
        @else
            @php
                $statusLabels = [
                    'backlog' => 'Бэклог',
                    'todo' => 'К выполнению',
                    'in_progress' => 'В процессе',
                    'done' => 'Завершено',
                ];
                $priorityLabels = [
                    'low' => 'Низкий',
                    'medium' => 'Средний',
                    'high' => 'Высокий',
                    'critical' => 'Критический',
                ];
            @endphp

            <div class="tasks-list">
                @foreach ($project->tasks as $task)
                    <div class="task-item">
                        <div class="task-item-left">
                            <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="task-status-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'todo' : 'done' }}">
                                <button
                                    type="submit"
                                    class="circular-checkbox-btn"
                                    title="{{ $task->status === 'done' ? 'Вернуть задачу в работу' : 'Отметить как выполненное' }}"
                                >
                                    <x-ui.checkbox :checked="$task->status === 'done'" />
                                </button>
                            </form>

                            <div class="task-item-content">
                                <a
                                    href="{{ route('tasks.edit', $task) }}"
                                    class="task-title {{ $task->status === 'done' ? 'is-completed' : '' }}"
                                    title="{{ $task->title }}"
                                >
                                    {{ $task->title }}
                                </a>

                                <div class="task-meta">
                                    <x-ui.badge type="status" :variant="$task->status">
                                        {{ $statusLabels[$task->status] ?? $task->status }}
                                    </x-ui.badge>

                                    <x-ui.badge type="priority" :variant="$task->priority">
                                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                    </x-ui.badge>

                                    @if ($task->isOverdue())
                                        <x-ui.badge type="deadline" variant="overdue">
                                            Просрочено: {{ $task->due_date->format('d.m.Y') }}
                                        </x-ui.badge>
                                    @elseif ($task->isDueToday())
                                        <x-ui.badge type="deadline" variant="today">
                                            Сегодня: {{ $task->due_date->format('d.m.Y') }}
                                        </x-ui.badge>
                                    @elseif ($task->due_date)
                                        <x-ui.badge type="deadline" variant="upcoming" :dot="false">
                                            {{ $task->due_date->format('d.m.Y') }}
                                        </x-ui.badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="task-item-actions">
                            <x-ui.button href="{{ route('tasks.edit', $task) }}" variant="secondary" size="sm" class="task-action-edit">
                                Изменить
                            </x-ui.button>

                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить задачу «{{ addslashes($task->title) }}»?');" class="task-action-delete-form">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm" title="Удалить задачу">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
