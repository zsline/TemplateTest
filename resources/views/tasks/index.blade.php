<x-app-layout title="Все задачи - TemplateTest">
    <div class="page-header">
        <div class="page-title-group">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h1 class="page-title">Все задачи</h1>
                <span class="tasks-found-badge" title="Количество найденных задач">
                    Найдено: {{ $tasks->count() }}
                </span>
            </div>
            <p class="page-subtitle">Централизованный список ваших текущих дел с фильтрацией и сортировкой</p>
        </div>
        <div class="page-actions">
            <x-ui.button href="{{ route('tasks.create') }}" variant="primary">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Новая задача</span>
            </x-ui.button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Всего задач</span>
                <span class="kpi-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value">{{ $totalCount }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">В процессе</span>
                <span class="kpi-card-icon" style="color: #3b82f6;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value" style="color: #3b82f6;">{{ $inProgressCount }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">К выполнению</span>
                <span class="kpi-card-icon" style="color: var(--color-brand-accent);">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value">{{ $todoCount }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Завершено</span>
                <span class="kpi-card-icon" style="color: #10b981;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value" style="color: #10b981;">{{ $completedCount }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Просрочено</span>
                <span class="kpi-card-icon" style="color: {{ $overdueCount > 0 ? '#ef4444' : 'var(--color-text-muted)' }};">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value" style="color: {{ $overdueCount > 0 ? '#ef4444' : 'var(--color-text-primary)' }};">{{ $overdueCount }}</div>
        </div>
    </div>

    <!-- Filter & Sort Panel (TASK-005) -->
    <form method="GET" action="{{ route('tasks.index') }}" class="tasks-filter-card">
        <div class="tasks-filter-grid">
            <!-- Проект -->
            <div class="tasks-filter-group">
                <label for="filter-project" class="tasks-filter-label">Проект</label>
                <select name="project_id" id="filter-project" class="form-select">
                    <option value="">Все проекты</option>
                    @foreach ($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}
                        >
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Статус -->
            <div class="tasks-filter-group">
                <label for="filter-status" class="tasks-filter-label">Статус</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Только активные</option>
                    <option value="backlog" {{ request('status') === 'backlog' ? 'selected' : '' }}>Бэклог</option>
                    <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>К выполнению</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Завершено</option>
                </select>
            </div>

            <!-- Приоритет -->
            <div class="tasks-filter-group">
                <label for="filter-priority" class="tasks-filter-label">Приоритет</label>
                <select name="priority" id="filter-priority" class="form-select">
                    <option value="">Все приоритеты</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Критический</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Высокий</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Средний</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Низкий</option>
                </select>
            </div>

            <!-- Срок выполнения (Дедлайн) -->
            @php
                $currentDeadline = request('deadline') ?? request('due_date');
            @endphp
            <div class="tasks-filter-group">
                <label for="filter-deadline" class="tasks-filter-label">Срок выполнения</label>
                <select name="deadline" id="filter-deadline" class="form-select">
                    <option value="">Все сроки</option>
                    <option value="overdue" {{ $currentDeadline === 'overdue' ? 'selected' : '' }}>Просроченные</option>
                    <option value="today" {{ $currentDeadline === 'today' ? 'selected' : '' }}>На сегодня</option>
                    <option value="upcoming" {{ $currentDeadline === 'upcoming' ? 'selected' : '' }}>Предстоящие</option>
                    <option value="no_due_date" {{ $currentDeadline === 'no_due_date' ? 'selected' : '' }}>Без срока</option>
                </select>
            </div>

            <!-- Сортировка -->
            <div class="tasks-filter-group">
                <label for="filter-sort" class="tasks-filter-label">Сортировка</label>
                <select name="sort" id="filter-sort" class="form-select">
                    <option value="created_at_desc" {{ request('sort', 'created_at_desc') === 'created_at_desc' ? 'selected' : '' }}>Сначала новые</option>
                    <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>Сначала старые</option>
                    <option value="due_date_asc" {{ request('sort') === 'due_date_asc' ? 'selected' : '' }}>Дедлайн: сначала близкие</option>
                    <option value="due_date_desc" {{ request('sort') === 'due_date_desc' ? 'selected' : '' }}>Дедлайн: сначала дальние</option>
                    <option value="priority_desc" {{ request('sort') === 'priority_desc' ? 'selected' : '' }}>Приоритет: от высокого</option>
                    <option value="priority_asc" {{ request('sort') === 'priority_asc' ? 'selected' : '' }}>Приоритет: от низкого</option>
                    <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>По названию (А–Я)</option>
                    <option value="title_desc" {{ request('sort') === 'title_desc' ? 'selected' : '' }}>По названию (Я–А)</option>
                </select>
            </div>
        </div>

        <div class="tasks-filter-actions">
            @php
                $hasActiveFilters = request()->filled('project_id')
                    || request()->filled('status')
                    || request()->filled('priority')
                    || request()->filled('deadline')
                    || request()->filled('due_date')
                    || (request()->filled('sort') && request('sort') !== 'created_at_desc');
            @endphp

            @if ($hasActiveFilters)
                <x-ui.button href="{{ route('tasks.index') }}" variant="secondary" size="sm">
                    Сбросить фильтры
                </x-ui.button>
            @endif

            <x-ui.button type="submit" variant="primary" size="sm">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Применить фильтры</span>
            </x-ui.button>
        </div>
    </form>

    <!-- Tasks List Container -->
    @if ($tasks->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            @if ($hasActiveFilters)
                <h3 class="empty-title">Задачи не найдены</h3>
                <p class="empty-text">По выбранным критериям фильтрации не найдено ни одной задачи. Попробуйте сбросить фильтры.</p>
                <x-ui.button href="{{ route('tasks.index') }}" variant="secondary" style="margin-top: 10px;">
                    Сбросить все фильтры
                </x-ui.button>
            @else
                <h3 class="empty-title">Задач пока нет</h3>
                <p class="empty-text">Создайте вашу первую задачу, привяжите её к проекту и установите дедлайн.</p>
                <x-ui.button href="{{ route('tasks.create') }}" variant="primary" style="margin-top: 10px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Создать задачу</span>
                </x-ui.button>
            @endif
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
            @foreach ($tasks as $task)
                <div class="task-item">
                    <div class="task-item-left">
                        <!-- Quick status toggle form -->
                        <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="task-status-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'todo' : 'done' }}">
                            <button
                                type="submit"
                                class="circular-checkbox-btn"
                                title="{{ $task->status === 'done' ? 'Вернуть задачу в работу' : 'Отметить задачу как выполненную' }}"
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
                                @if ($task->project)
                                    <span class="task-project-pill">
                                        <span class="project-color-dot" style="background-color: {{ $task->project->color }};"></span>
                                        <span>{{ $task->project->name }}</span>
                                    </span>
                                @endif

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
                                @else
                                    <span style="font-size: 11px; color: var(--color-text-muted);">
                                        Без срока
                                    </span>
                                @endif

                                @if ($task->status === 'done' && $task->completed_at)
                                    <span style="font-size: 11px; color: var(--color-text-muted);">
                                        Завершена {{ $task->completed_at->format('d.m.Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="task-item-actions">
                        <x-ui.button href="{{ route('tasks.edit', $task) }}" variant="secondary" size="sm" class="task-action-edit">
                            Изменить
                        </x-ui.button>

                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить задачу «{{ addslashes($task->title) }}»?');" class="task-action-delete-form">
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
</x-app-layout>
