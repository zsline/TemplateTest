<x-app-layout title="Мои проекты - TemplateTest">
    <div class="page-header">
        <div class="page-title-group">
            <h1 class="page-title">Мои проекты</h1>
            <p class="page-subtitle">Организуйте работу по направлениям и отслеживайте их состояние</p>
        </div>
        <div class="page-actions">
            <x-ui.button href="{{ route('projects.create') }}" variant="primary">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Создать проект</span>
            </x-ui.button>
        </div>
    </div>

    <!-- KPI Metrics -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Всего проектов</span>
                <span class="kpi-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value">{{ $totalProjectsCount }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Активных задач</span>
                <span class="kpi-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value">
                @if ($hasTasks)
                    {{ $projects->sum('tasks_count') }}
                @else
                    <span style="font-size: 16px; font-weight: 500; color: var(--color-text-muted);">TASK-004</span>
                @endif
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-card-label">Статус интеграции</span>
                <span class="kpi-card-icon" style="color: #10b981;">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="kpi-card-value" style="font-size: 20px; color: #10b981;">
                Изолировано
            </div>
        </div>
    </div>

    @if ($projects->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                </svg>
            </div>
            <h3 class="empty-title">У вас пока нет ни одного проекта</h3>
            <p class="empty-text">Создайте свой первый проект, чтобы группировать задачи и отслеживать прогресс выполнения.</p>
            <x-ui.button href="{{ route('projects.create') }}" variant="primary" style="margin-top: 10px;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Создать проект</span>
            </x-ui.button>
        </div>
    @else
        <div class="projects-grid">
            @foreach ($projects as $project)
                <div class="project-card">
                    <div class="project-card-top">
                        <a href="{{ route('projects.show', $project) }}" class="project-title-link">
                            <span class="project-color-badge" style="background-color: {{ $project->color }};"></span>
                            <span>{{ $project->name }}</span>
                        </a>
                    </div>

                    <div class="project-card-desc">
                        {{ $project->description ?: 'Описание проекта не указано.' }}
                    </div>

                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-track">
                            <div class="progress-bar-fill" style="width: 0%; background: {{ $project->color }};"></div>
                        </div>
                        <div class="progress-bar-info">
                            @if ($hasTasks)
                                <span>{{ $project->tasks_count }} задач</span>
                            @else
                                <span style="font-size: 11px; color: var(--color-text-muted);">Задачи: модуль TASK-004</span>
                            @endif
                            <span>{{ $project->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>

                    <div class="project-card-footer">
                        <div class="project-card-actions">
                            <x-ui.button href="{{ route('projects.show', $project) }}" variant="secondary" size="sm">
                                Просмотр
                            </x-ui.button>
                            <x-ui.button href="{{ route('projects.edit', $project) }}" variant="secondary" size="sm">
                                Изменить
                            </x-ui.button>
                        </div>

                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить проект «{{ addslashes($project->name) }}»?');">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm" title="Удалить проект">
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
