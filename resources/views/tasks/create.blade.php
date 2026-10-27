<x-app-layout title="Создать задачу - TemplateTest">
    <div class="breadcrumbs">
        <a href="{{ route('tasks.index') }}" class="breadcrumb-link">Задачи</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Новая задача</span>
    </div>

    <div class="page-header">
        <div class="page-title-group">
            <h1 class="page-title">Создать задачу</h1>
            <p class="page-subtitle">Добавьте новую задачу, выберите проект, установите приоритет и дедлайн</p>
        </div>
    </div>

    @if ($projects->isEmpty())
        <x-ui.alert type="warning" title="Требуется проект">
            Перед созданием задачи необходимо создать проект. Все задачи строго привязываются к проектам.
            <div style="margin-top: 14px;">
                <x-ui.button href="{{ route('projects.create') }}" variant="primary">
                    Создать первый проект
                </x-ui.button>
            </div>
        </x-ui.alert>
    @else
        <div style="max-width: 680px;">
            <x-ui.card>
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf

                    <x-ui.input
                        name="title"
                        label="Название задачи"
                        placeholder="Например: Разработать макет главной страницы"
                        required
                        autofocus
                    />

                    <div class="form-group">
                        <label for="project_id" class="form-label">
                            Проект
                            <span style="color: var(--color-brand-accent);">*</span>
                        </label>
                        <select
                            name="project_id"
                            id="project_id"
                            class="form-select {{ $errors->has('project_id') ? 'has-error' : '' }}"
                            required
                        >
                            <option value="">-- Выберите проект --</option>
                            @foreach ($projects as $project)
                                <option
                                    value="{{ $project->id }}"
                                    {{ (string) old('project_id', $selectedProjectId) === (string) $project->id ? 'selected' : '' }}
                                >
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="status" class="form-label">
                                Статус
                                <span style="color: var(--color-brand-accent);">*</span>
                            </label>
                            <select
                                name="status"
                                id="status"
                                class="form-select {{ $errors->has('status') ? 'has-error' : '' }}"
                                required
                            >
                                @php $currentStatus = old('status', 'todo'); @endphp
                                <option value="backlog" {{ $currentStatus === 'backlog' ? 'selected' : '' }}>Бэклог</option>
                                <option value="todo" {{ $currentStatus === 'todo' ? 'selected' : '' }}>К выполнению</option>
                                <option value="in_progress" {{ $currentStatus === 'in_progress' ? 'selected' : '' }}>В процессе</option>
                                <option value="done" {{ $currentStatus === 'done' ? 'selected' : '' }}>Завершено</option>
                            </select>
                            @error('status')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="priority" class="form-label">
                                Приоритет
                                <span style="color: var(--color-brand-accent);">*</span>
                            </label>
                            <select
                                name="priority"
                                id="priority"
                                class="form-select {{ $errors->has('priority') ? 'has-error' : '' }}"
                                required
                            >
                                @php $currentPriority = old('priority', 'medium'); @endphp
                                <option value="low" {{ $currentPriority === 'low' ? 'selected' : '' }}>Низкий</option>
                                <option value="medium" {{ $currentPriority === 'medium' ? 'selected' : '' }}>Средний</option>
                                <option value="high" {{ $currentPriority === 'high' ? 'selected' : '' }}>Высокий</option>
                                <option value="critical" {{ $currentPriority === 'critical' ? 'selected' : '' }}>Критический</option>
                            </select>
                            @error('priority')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="due_date" class="form-label">
                            Срок исполнения (дедлайн)
                            <span class="form-label-optional">(опционально)</span>
                        </label>
                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            value="{{ old('due_date') }}"
                            class="form-input {{ $errors->has('due_date') ? 'has-error' : '' }}"
                        >
                        @error('due_date')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            Описание задачи
                            <span class="form-label-optional">(опционально)</span>
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            placeholder="Подробности, требования или заметки..."
                            class="form-textarea {{ $errors->has('description') ? 'has-error' : '' }}"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; margin-top: 28px;">
                        <x-ui.button type="submit" variant="primary">
                            Создать задачу
                        </x-ui.button>
                        <x-ui.button href="{{ route('tasks.index') }}" variant="secondary">
                            Отмена
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    @endif
</x-app-layout>
