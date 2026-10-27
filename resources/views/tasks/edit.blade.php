<x-app-layout title="Редактировать {{ $task->title }} - TemplateTest">
    <div class="breadcrumbs">
        <a href="{{ route('tasks.index') }}" class="breadcrumb-link">Задачи</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $task->title }}</span>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Редактирование</span>
    </div>

    <div class="page-header">
        <div class="page-title-group">
            <h1 class="page-title">Редактировать задачу</h1>
            <p class="page-subtitle">Обновите параметры задачи, смените статус или проект</p>
        </div>
        <div class="page-actions">
            @if ($task->completed_at)
                <x-ui.badge type="status" variant="done">
                    Завершена {{ $task->completed_at->format('d.m.Y в H:i') }}
                </x-ui.badge>
            @endif

            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить задачу «{{ addslashes($task->title) }}»?');" style="margin: 0;">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    <span>Удалить задачу</span>
                </x-ui.button>
            </form>
        </div>
    </div>

    <div style="max-width: 680px;">
        <x-ui.card>
            <form action="{{ route('tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                <x-ui.input
                    name="title"
                    label="Название задачи"
                    :value="$task->title"
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
                        @foreach ($projects as $project)
                            <option
                                value="{{ $project->id }}"
                                {{ (string) old('project_id', $task->project_id) === (string) $project->id ? 'selected' : '' }}
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
                            @php $currentStatus = old('status', $task->status); @endphp
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
                            @php $currentPriority = old('priority', $task->priority); @endphp
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
                        value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
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
                        class="form-textarea {{ $errors->has('description') ? 'has-error' : '' }}"
                    >{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; align-items: center; gap: 12px; margin-top: 28px;">
                    <x-ui.button type="submit" variant="primary">
                        Сохранить изменения
                    </x-ui.button>
                    <x-ui.button href="{{ route('tasks.index') }}" variant="secondary">
                        Отмена
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
