<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Список проектов текущего пользователя.
     */
    public function index(Request $request): View
    {
        $query = $request->user()->projects();

        $hasTasks = class_exists(Task::class) && Schema::hasTable('tasks');
        if ($hasTasks) {
            $query->withCount('tasks');
        }

        $projects = $query->latest()->get();
        $totalProjectsCount = $projects->count();

        return view('projects.index', compact('projects', 'totalProjectsCount', 'hasTasks'));
    }

    /**
     * Форма создания проекта.
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * Сохранение нового проекта.
     */
    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект «'.$project->name.'» успешно создан!');
    }

    /**
     * Просмотр проекта и списка его задач.
     */
    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $hasTasks = class_exists(Task::class) && Schema::hasTable('tasks');
        if ($hasTasks) {
            $project->load(['tasks' => function ($query) {
                $query->with('project')->latest();
            }]);
        }

        return view('projects.show', compact('project', 'hasTasks'));
    }

    /**
     * Форма редактирования проекта.
     */
    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Обновление проекта.
     */
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект «'.$project->name.'» успешно обновлён!');
    }

    /**
     * Удаление проекта.
     */
    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Проект «'.$name.'» успешно удалён.');
    }
}
