<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_belongs_to_user_and_has_many_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        Task::factory()->count(3)->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(User::class, $project->user);
        $this->assertEquals($user->id, $project->user->id);
        $this->assertCount(3, $project->tasks);
        $this->assertInstanceOf(Task::class, $project->tasks->first());
    }
}
