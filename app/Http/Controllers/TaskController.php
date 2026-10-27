<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskFilterService $filterService
    ) {}

    /**
     * Список задач текущего пользователя с фильтрацией и сортировкой.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Проекты пользователя для селектора фильтра
        $projects = $user->projects()->orderBy('name')->get();

        // 2. Запрос задач с жадной загрузкой проектов и применением фильтров
        $baseQuery = Task::where('user_id', $user->id)->with('project');
        $tasks = $this->filterService->apply($baseQuery, $request->all())->get();

        // 3. Вычисление KPI-метрик в памяти (без дополнительных SQL-запросов)
        $totalCount = $tasks->count();
        $completedCount = $tasks->where('status', 'done')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $todoCount = $tasks->where('status', 'todo')->count();
        $backlogCount = $tasks->where('status', 'backlog')->count();
        $overdueCount = $tasks->filter(fn (Task $t) => $t->isOverdue())->count();

        $filters = $request->all();

        return view('tasks.index', compact(
            'tasks',
            'projects',
            'filters',
            'totalCount',
            'completedCount',
            'inProgressCount',
            'todoCount',
            'backlogCount',
            'overdueCount'
        ));
    }

    /**
     * Форма создания новой задачи.
     */
    public function create(Request $request): View
    {
        $projects = $request->user()->projects()->orderBy('name')->get();
        $selectedProjectId = $request->query('project_id');

        return view('tasks.create', compact('projects', 'selectedProjectId'));
    }

    /**
     * Сохранение новой задачи.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Задача «'.$task->title.'» успешно создана!');
    }

    /**
     * Просмотр задачи (перенаправление на форму редактирования).
     */
    public function show(Task $task): RedirectResponse
    {
        Gate::authorize('view', $task);

        return redirect()->route('tasks.edit', $task);
    }

    /**
     * Форма редактирования задачи.
     */
    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        $projects = auth()->user()->projects()->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    /**
     * Обновление параметров задачи.
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->update($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Задача «'.$task->title.'» успешно обновлена!');
    }

    /**
     * Быстрое обновление статуса задачи.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['backlog', 'todo', 'in_progress', 'done'])],
        ]);

        $task->update([
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'completed_at' => $task->completed_at?->toIso8601String(),
            ]);
        }

        return back()->with('success', 'Статус задачи успешно обновлён.');
    }

    /**
     * Удаление задачи.
     */
    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Задача «'.$title.'» успешно удалена.');
    }
}
