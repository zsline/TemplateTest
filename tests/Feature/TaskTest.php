<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_tasks_routes(): void
    {
        $task = Task::factory()->create();

        $this->get(route('tasks.index'))->assertRedirect(route('login'));
        $this->get(route('tasks.create'))->assertRedirect(route('login'));
        $this->post(route('tasks.store'), [])->assertRedirect(route('login'));
        $this->get(route('tasks.edit', $task))->assertRedirect(route('login'));
        $this->put(route('tasks.update', $task), [])->assertRedirect(route('login'));
        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('login'));
        $this->patch(route('tasks.update-status', $task), ['status' => 'done'])->assertRedirect(route('login'));
    }

    public function test_users_can_view_tasks_index_and_only_see_their_own_tasks(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectA = Project::factory()->create(['user_id' => $userA->id]);
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $taskA = Task::factory()->create([
            'user_id' => $userA->id,
            'project_id' => $projectA->id,
            'title' => 'Задача Пользователя А',
        ]);

        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
            'title' => 'Задача Пользователя Б',
        ]);

        $response = $this->actingAs($userA)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Задача Пользователя А');
        $response->assertDontSee('Задача Пользователя Б');
    }

    public function test_users_can_create_task_with_valid_data(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'project_id' => $project->id,
            'title' => 'Сверстать макет личного кабинета',
            'description' => 'Необходимо учесть мобильную адаптивность',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Сверстать макет личного кабинета',
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    public function test_task_creation_validation_rules(): void
    {
        $user = User::factory()->create();

        // Пустые обязательные поля
        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => '',
            'project_id' => '',
            'status' => 'invalid-status',
            'priority' => 'invalid-priority',
            'due_date' => 'not-a-date',
        ]);

        $response->assertSessionHasErrors(['title', 'project_id', 'status', 'priority', 'due_date']);
        $this->assertEquals(0, Task::count());
    }

    public function test_users_cannot_create_task_with_another_users_project(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->post(route('tasks.store'), [
            'project_id' => $projectB->id,
            'title' => 'Попытка привязки к чужому проекту',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response->assertSessionHasErrors(['project_id']);
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Попытка привязки к чужому проекту',
        ]);
    }

    public function test_users_cannot_update_task_with_another_users_project(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectA = Project::factory()->create(['user_id' => $userA->id]);
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $taskA = Task::factory()->create([
            'user_id' => $userA->id,
            'project_id' => $projectA->id,
            'title' => 'Моя задача',
        ]);

        $response = $this->actingAs($userA)->put(route('tasks.update', $taskA), [
            'project_id' => $projectB->id, // Чужой проект!
            'title' => 'Обновлённая задача',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response->assertSessionHasErrors(['project_id']);
        $taskA->refresh();
        $this->assertEquals($projectA->id, $taskA->project_id);
    }

    public function test_users_can_view_edit_form_for_their_own_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача на редактирование',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertStatus(200);
        $response->assertSee('Задача на редактирование');
    }

    public function test_users_can_view_their_own_task_and_are_redirected_to_edit(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Моя задача',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertRedirect(route('tasks.edit', $task));
    }

    public function test_users_cannot_view_other_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
        ]);

        $response = $this->actingAs($userA)->get(route('tasks.show', $taskB));

        $response->assertStatus(403);
    }

    public function test_users_cannot_view_edit_form_for_other_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
        ]);

        $response = $this->actingAs($userA)->get(route('tasks.edit', $taskB));

        $response->assertStatus(403);
    }

    public function test_users_can_update_their_own_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Старое название задачи',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'project_id' => $project->id,
            'title' => 'Новое название задачи',
            'description' => 'Обновлённое описание',
            'status' => 'in_progress',
            'priority' => 'critical',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $task->refresh();
        $this->assertEquals('Новое название задачи', $task->title);
        $this->assertEquals('Обновлённое описание', $task->description);
        $this->assertEquals('in_progress', $task->status);
        $this->assertEquals('critical', $task->priority);
    }

    public function test_users_cannot_update_other_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
            'title' => 'Задача пользователя Б',
        ]);

        $response = $this->actingAs($userA)->put(route('tasks.update', $taskB), [
            'project_id' => $projectB->id,
            'title' => 'Взлом задачи',
            'status' => 'done',
            'priority' => 'critical',
        ]);

        $response->assertStatus(403);
        $taskB->refresh();
        $this->assertEquals('Задача пользователя Б', $taskB->title);
    }

    public function test_users_can_delete_their_own_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_users_cannot_delete_other_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
        ]);

        $response = $this->actingAs($userA)->delete(route('tasks.destroy', $taskB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $taskB->id]);
    }

    public function test_users_can_update_task_status_via_patch_route(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($user)->patch(route('tasks.update-status', $task), [
            'status' => 'done',
        ]);

        $response->assertSessionHas('success');
        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_users_cannot_update_status_of_other_users_task(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);
        $taskB = Task::factory()->create([
            'user_id' => $userB->id,
            'project_id' => $projectB->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($userA)->patch(route('tasks.update-status', $taskB), [
            'status' => 'done',
        ]);

        $response->assertStatus(403);
        $taskB->refresh();
        $this->assertEquals('todo', $taskB->status);
    }

    public function test_completed_at_lifecycle_on_status_transitions(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // 1. Создание задачи в todo - completed_at должен быть null
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'completed_at' => null,
        ]);
        $this->assertNull($task->completed_at);

        // 2. Переход в done - completed_at устанавливается на текущее время
        $task->update(['status' => 'done']);
        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->completed_at);
        $originalCompletedAt = $task->completed_at->toIso8601String();
        $this->assertEquals('2026-10-27T12:00:00+00:00', $task->completed_at->toIso8601String());

        // 3. Задача остаётся в done, меняется только название спустя 2 часа - первоначальное completed_at сохраняется
        Carbon::setTestNow('2026-10-27 14:00:00');
        $task->update(['title' => 'Обновленное название задачи']);
        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertEquals($originalCompletedAt, $task->completed_at->toIso8601String());

        // 4. Переход из done в in_progress - completed_at сбрасывается в null
        $task->update(['status' => 'in_progress']);
        $task->refresh();
        $this->assertEquals('in_progress', $task->status);
        $this->assertNull($task->completed_at);

        // 5. Повторный переход в done в новое время - устанавливается новое время
        Carbon::setTestNow('2026-10-27 16:30:00');
        $task->update(['status' => 'done']);
        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertEquals('2026-10-27T16:30:00+00:00', $task->completed_at->toIso8601String());

        Carbon::setTestNow(); // сброс фиктивного времени
    }

    public function test_is_overdue_and_is_due_today_methods(): void
    {
        Carbon::setTestNow('2026-10-27 10:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Вчерашняя незавершенная задача -> просрочена, но не сегодня
        $overdueTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => '2026-10-26',
        ]);
        $this->assertTrue($overdueTask->isOverdue());
        $this->assertFalse($overdueTask->isDueToday());

        // Вчерашняя, но завершенная задача -> не считается просроченной
        $completedOverdueTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'done',
            'due_date' => '2026-10-26',
        ]);
        $this->assertFalse($completedOverdueTask->isOverdue());
        $this->assertFalse($completedOverdueTask->isDueToday());

        // Сегодняшняя незавершенная задача -> на сегодня, но не просрочена
        $todayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'in_progress',
            'due_date' => '2026-10-27',
        ]);
        $this->assertFalse($todayTask->isOverdue());
        $this->assertTrue($todayTask->isDueToday());

        // Сегодняшняя завершенная задача -> не на сегодня
        $todayDoneTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'done',
            'due_date' => '2026-10-27',
        ]);
        $this->assertFalse($todayDoneTask->isOverdue());
        $this->assertFalse($todayDoneTask->isDueToday());

        // Будущая задача -> ни то, ни другое
        $futureTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => '2026-10-28',
        ]);
        $this->assertFalse($futureTask->isOverdue());
        $this->assertFalse($futureTask->isDueToday());

        // Задача без дедлайна -> ни то, ни другое
        $noDueTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => null,
        ]);
        $this->assertFalse($noDueTask->isOverdue());
        $this->assertFalse($noDueTask->isDueToday());

        Carbon::setTestNow();
    }
}
