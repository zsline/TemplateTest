<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_project(): void
    {
        $user = User::factory()->create();
        $projectA = Project::factory()->create(['user_id' => $user->id, 'name' => 'Проект Альфа']);
        $projectB = Project::factory()->create(['user_id' => $user->id, 'name' => 'Проект Бета']);

        $taskA = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectA->id,
            'title' => 'Задача Альфа',
        ]);
        $taskB = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectB->id,
            'title' => 'Задача Бета',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['project_id' => $projectA->id]));

        $response->assertStatus(200);
        $response->assertSee('Задача Альфа');
        $response->assertDontSee('Задача Бета');
    }

    public function test_filter_by_status_single_and_active(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskBacklog = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'Бэклог таска', 'status' => 'backlog']);
        $taskTodo = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'Туду таска', 'status' => 'todo']);
        $taskInProgress = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'В работе таска', 'status' => 'in_progress']);
        $taskDone = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'Готовая таска', 'status' => 'done']);

        // 1. Фильтр по конкретному статусу 'in_progress'
        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'in_progress']));
        $response->assertStatus(200);
        $response->assertSee('В работе таска');
        $response->assertDontSee('Бэклог таска');
        $response->assertDontSee('Туду таска');
        $response->assertDontSee('Готовая таска');

        // 2. Фильтр 'active' (все незавершенные задачи: backlog, todo, in_progress)
        $responseActive = $this->actingAs($user)->get(route('tasks.index', ['status' => 'active']));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Бэклог таска');
        $responseActive->assertSee('Туду таска');
        $responseActive->assertSee('В работе таска');
        $responseActive->assertDontSee('Готовая таска');

        // 3. Фильтр 'done'
        $responseDone = $this->actingAs($user)->get(route('tasks.index', ['status' => 'done']));
        $responseDone->assertStatus(200);
        $responseDone->assertSee('Готовая таска');
        $responseDone->assertDontSee('В работе таска');
    }

    public function test_filter_by_priority(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskCritical = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'Критический баг', 'priority' => 'critical']);
        $taskLow = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id, 'title' => 'Низкий приоритет', 'priority' => 'low']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['priority' => 'critical']));

        $response->assertStatus(200);
        $response->assertSee('Критический баг');
        $response->assertDontSee('Низкий приоритет');
    }

    public function test_filter_by_deadline_overdue(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $overdueActive = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Просрочена и активна',
            'status' => 'todo',
            'due_date' => '2026-10-25',
        ]);

        $overdueDone = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Просрочена но завершена',
            'status' => 'done',
            'due_date' => '2026-10-25',
        ]);

        $todayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Сегодняшняя задача',
            'status' => 'todo',
            'due_date' => '2026-10-27',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['deadline' => 'overdue']));

        $response->assertStatus(200);
        $response->assertSee('Просрочена и активна');
        $response->assertDontSee('Просрочена но завершена');
        $response->assertDontSee('Сегодняшняя задача');

        Carbon::setTestNow();
    }

    public function test_filter_by_deadline_today(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Задача на сегодня (active)
        $todayActive = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Сегодня активная',
            'status' => 'in_progress',
            'due_date' => '2026-10-27',
        ]);

        // Задача на сегодня (done) - по ТЗ: due_date = today без ограничения по статусу
        $todayDone = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Сегодня завершенная',
            'status' => 'done',
            'due_date' => '2026-10-27',
        ]);

        // Задача на вчера
        $yesterdayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Вчерашняя задача',
            'due_date' => '2026-10-26',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['deadline' => 'today']));

        $response->assertStatus(200);
        $response->assertSee('Сегодня активная');
        $response->assertSee('Сегодня завершенная');
        $response->assertDontSee('Вчерашняя задача');

        Carbon::setTestNow();
    }

    public function test_filter_by_deadline_upcoming_and_no_due_date(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $upcomingTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Будущая задача',
            'due_date' => '2026-10-30',
        ]);

        $noDueDateTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача без срока',
            'due_date' => null,
        ]);

        // Проверка upcoming
        $responseUpcoming = $this->actingAs($user)->get(route('tasks.index', ['deadline' => 'upcoming']));
        $responseUpcoming->assertStatus(200);
        $responseUpcoming->assertSee('Будущая задача');
        $responseUpcoming->assertDontSee('Задача без срока');

        // Проверка no_due_date
        $responseNoDueDate = $this->actingAs($user)->get(route('tasks.index', ['deadline' => 'no_due_date']));
        $responseNoDueDate->assertStatus(200);
        $responseNoDueDate->assertSee('Задача без срока');
        $responseNoDueDate->assertDontSee('Будущая задача');

        Carbon::setTestNow();
    }

    public function test_combination_of_filters_works_as_and(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $projectA = Project::factory()->create(['user_id' => $user->id]);
        $projectB = Project::factory()->create(['user_id' => $user->id]);

        // Целевая задача (соответствует всем фильтрам)
        $targetTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectA->id,
            'title' => 'Идеальное совпадение',
            'status' => 'in_progress',
            'priority' => 'critical',
            'due_date' => '2026-10-27',
        ]);

        // Не подходит по проекту
        $otherProjectTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectB->id,
            'title' => 'Не тот проект',
            'status' => 'in_progress',
            'priority' => 'critical',
            'due_date' => '2026-10-27',
        ]);

        // Не подходит по статусу
        $otherStatusTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectA->id,
            'title' => 'Не тот статус',
            'status' => 'todo',
            'priority' => 'critical',
            'due_date' => '2026-10-27',
        ]);

        // Не подходит по приоритету
        $otherPriorityTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $projectA->id,
            'title' => 'Не тот приоритет',
            'status' => 'in_progress',
            'priority' => 'low',
            'due_date' => '2026-10-27',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'project_id' => $projectA->id,
            'status' => 'in_progress',
            'priority' => 'critical',
            'deadline' => 'today',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Идеальное совпадение');
        $response->assertDontSee('Не тот проект');
        $response->assertDontSee('Не тот статус');
        $response->assertDontSee('Не тот приоритет');

        Carbon::setTestNow();
    }

    public function test_sorting_options(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskLow = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача Низкая',
            'priority' => 'low',
            'due_date' => '2026-11-20',
        ]);

        $taskCritical = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача Критическая',
            'priority' => 'critical',
            'due_date' => '2026-11-05',
        ]);

        // Сортировка по приоритету (priority_desc): критическая первой
        $responsePriority = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'priority_desc']));
        $responsePriority->assertStatus(200);
        $responsePriority->assertSeeInOrder(['Задача Критическая', 'Задача Низкая']);

        // Сортировка по дедлайну (due_date_asc): 5 ноября раньше 20 ноября
        $responseDue = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'due_date_asc']));
        $responseDue->assertStatus(200);
        $responseDue->assertSeeInOrder(['Задача Критическая', 'Задача Низкая']);
    }

    public function test_invalid_filter_and_sort_values_are_safely_ignored(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Обычная задача',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'status' => 'malicious_status',
            'priority' => 'drop table tasks',
            'deadline' => 'invalid',
            'sort' => 'invalid_order_by',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Обычная задача');
    }

    public function test_filter_user_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
            'title' => 'Секретная задача Пользователя Б',
        ]);

        // Пользователь А пытается отфильтровать по ID чужого проекта
        $response = $this->actingAs($userA)->get(route('tasks.index', ['project_id' => $projectB->id]));

        $response->assertStatus(200);
        $response->assertDontSee('Секретная задача Пользователя Б');
    }

    public function test_eager_loading_prevents_n_plus_one_queries(): void
    {
        $user = User::factory()->create();

        // Создаем 8 задач в 8 разных проектах
        for ($i = 1; $i <= 8; $i++) {
            $project = Project::factory()->create(['user_id' => $user->id, 'name' => "Проект {$i}"]);
            Task::factory()->create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'title' => "Задача {$i}",
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get(route('tasks.index'));
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Подсчитываем запросы к таблицам (учитывая и MySQL backticks, и SQLite double-quotes)
        $projectQueries = array_filter($queries, fn ($q) => preg_match('/from\s+[`"]?projects[`"]?/i', $q['query']));
        $taskQueries = array_filter($queries, fn ($q) => preg_match('/from\s+[`"]?tasks[`"]?/i', $q['query']));

        // Должен быть ровно 1 запрос на проекты пользователя для селектора
        // и ровно 1 запрос жадной загрузки проектов для задач (WHERE id IN (...))
        // Итого ровно 2 запроса к таблице projects, вне зависимости от числа задач (не N+1)!
        $this->assertCount(2, $projectQueries, 'Запросов к projects должно быть ровно 2: для селектора и eager loading');
        $this->assertCount(1, $taskQueries, 'Запрос к tasks должен быть ровно один');

        // Общее число запросов для выборки данных не должно превышать 3-4 (включая пользователя сессии)
        $this->assertLessThanOrEqual(4, count($queries), 'Всего запросов к БД должно быть не более 4');
    }
}
