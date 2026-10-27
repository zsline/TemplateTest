<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_applies_default_sort_when_sort_is_missing_or_invalid(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskOld = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Старая задача',
            'created_at' => now()->subDays(5),
        ]);

        $taskNew = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Новая задача',
            'created_at' => now(),
        ]);

        $service = new TaskFilterService;

        // 1. Без параметра sort
        $query1 = Task::where('user_id', $user->id);
        $result1 = $service->apply($query1, [])->get();
        $this->assertEquals($taskNew->id, $result1->first()->id);

        // 2. С некорректным значением sort (должен примениться fallback)
        $query2 = Task::where('user_id', $user->id);
        $result2 = $service->apply($query2, ['sort' => 'malicious_order_by'])->get();
        $this->assertEquals($taskNew->id, $result2->first()->id);
    }

    public function test_service_sorts_by_priority_in_both_directions(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $taskLow = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'priority' => 'low',
        ]);
        $taskCritical = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'priority' => 'critical',
        ]);

        $service = new TaskFilterService;

        // По убыванию: критический сначала
        $queryDesc = Task::where('user_id', $user->id);
        $resDesc = $service->apply($queryDesc, ['sort' => 'priority_desc'])->get();
        $this->assertEquals($taskCritical->id, $resDesc->first()->id);

        // По возрастанию: низкий сначала
        $queryAsc = Task::where('user_id', $user->id);
        $resAsc = $service->apply($queryAsc, ['sort' => 'priority_asc'])->get();
        $this->assertEquals($taskLow->id, $resAsc->first()->id);
    }
}
