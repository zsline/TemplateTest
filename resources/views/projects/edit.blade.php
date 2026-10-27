<x-app-layout title="Редактировать {{ $project->name }} - TemplateTest">
    <div class="breadcrumbs">
        <a href="{{ route('projects.index') }}" class="breadcrumb-link">Проекты</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('projects.show', $project) }}" class="breadcrumb-link">{{ $project->name }}</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Редактирование</span>
    </div>

    <div class="page-header">
        <div class="page-title-group">
            <h1 class="page-title">Редактировать проект</h1>
            <p class="page-subtitle">Измените параметры проекта и сохраните обновления</p>
        </div>
    </div>

    <div style="max-width: 640px;">
        <x-ui.card>
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')

                <x-ui.input
                    name="name"
                    label="Название проекта"
                    :value="$project->name"
                    required
                    autofocus
                />

                <div class="form-group">
                    <label for="description" class="form-label">
                        Описание проекта
                        <span class="form-label-optional">(опционально)</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="form-textarea {{ $errors->has('description') ? 'has-error' : '' }}"
                    >{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Цвет проекта</label>

                    @php
                        $selectedColor = old('color', $project->color);
                        $presets = ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
                    @endphp

                    <div class="color-picker-palette">
                        @foreach ($presets as $preset)
                            <label class="color-swatch-label" title="{{ $preset }}">
                                <input
                                    type="radio"
                                    name="color_preset"
                                    value="{{ $preset }}"
                                    {{ strtolower($selectedColor) === strtolower($preset) ? 'checked' : '' }}
                                    onchange="document.getElementById('color-input').value = this.value; document.getElementById('native-color-picker').value = this.value;"
                                >
                                <span class="color-swatch" style="background-color: {{ $preset }};"></span>
                            </label>
                        @endforeach
                    </div>

                    <div class="color-custom-input-group">
                        <input
                            type="color"
                            id="native-color-picker"
                            value="{{ $selectedColor }}"
                            class="color-native-input"
                            onchange="document.getElementById('color-input').value = this.value;"
                        >
                        <input
                            type="text"
                            name="color"
                            id="color-input"
                            value="{{ $selectedColor }}"
                            class="form-input {{ $errors->has('color') ? 'has-error' : '' }}"
                            style="max-width: 140px; font-family: monospace;"
                            required
                        >
                    </div>

                    @error('color')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; align-items: center; gap: 12px; margin-top: 28px;">
                    <x-ui.button type="submit" variant="primary">
                        Сохранить изменения
                    </x-ui.button>
                    <x-ui.button href="{{ route('projects.show', $project) }}" variant="secondary">
                        Отмена
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
