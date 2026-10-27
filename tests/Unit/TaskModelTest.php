<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_user_and_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
        $this->assertInstanceOf(Project::class, $task->project);
        $this->assertEquals($project->id, $task->project->id);
    }

    public function test_is_overdue_method(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $pastTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => '2026-10-25',
        ]);
        $this->assertTrue($pastTask->isOverdue());

        $doneTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'done',
            'due_date' => '2026-10-25',
        ]);
        $this->assertFalse($doneTask->isOverdue());

        $noDueDateTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => null,
        ]);
        $this->assertFalse($noDueDateTask->isOverdue());

        $todayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => '2026-10-27',
        ]);
        $this->assertFalse($todayTask->isOverdue());

        Carbon::setTestNow();
    }

    public function test_is_due_today_method(): void
    {
        Carbon::setTestNow('2026-10-27 12:00:00');

        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $todayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'in_progress',
            'due_date' => '2026-10-27',
        ]);
        $this->assertTrue($todayTask->isDueToday());

        $doneTodayTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'done',
            'due_date' => '2026-10-27',
        ]);
        $this->assertFalse($doneTodayTask->isDueToday());

        $tomorrowTask = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'todo',
            'due_date' => '2026-10-28',
        ]);
        $this->assertFalse($tomorrowTask->isDueToday());

        Carbon::setTestNow();
    }
}
