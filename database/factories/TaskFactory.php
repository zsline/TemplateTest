<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => function (array $attributes) {
                return Project::factory()->create([
                    'user_id' => $attributes['user_id'],
                ])->id;
            },
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'completed_at' => null,
        ];
    }
}
