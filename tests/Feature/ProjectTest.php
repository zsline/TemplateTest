<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_project_routes(): void
    {
        $project = Project::factory()->create();

        $this->get(route('projects.index'))->assertRedirect(route('login'));
        $this->get(route('projects.create'))->assertRedirect(route('login'));
        $this->post(route('projects.store'), [])->assertRedirect(route('login'));
        $this->get(route('projects.show', $project))->assertRedirect(route('login'));
        $this->get(route('projects.edit', $project))->assertRedirect(route('login'));
        $this->put(route('projects.update', $project), [])->assertRedirect(route('login'));
        $this->delete(route('projects.destroy', $project))->assertRedirect(route('login'));
    }

    public function test_users_can_view_projects_index_and_only_see_their_own_projects(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectA = Project::factory()->create([
            'user_id' => $userA->id,
            'name' => 'Проект Пользователя А',
        ]);

        $projectB = Project::factory()->create([
            'user_id' => $userB->id,
            'name' => 'Проект Пользователя Б',
        ]);

        $response = $this->actingAs($userA)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Проект Пользователя А');
        $response->assertDontSee('Проект Пользователя Б');
    }

    public function test_users_can_create_projects_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Новый SaaS Продукт',
            'description' => 'Описание нового крутого продукта',
            'color' => '#10b981',
        ]);

        $project = Project::where('name', 'Новый SaaS Продукт')->first();
        $this->assertNotNull($project);
        $this->assertEquals($user->id, $project->user_id);
        $this->assertEquals('#10b981', $project->color);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success');
    }

    public function test_project_creation_requires_valid_name_and_hex_color(): void
    {
        $user = User::factory()->create();

        // Пустое имя и неверный цвет
        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => '',
            'description' => 'Описание',
            'color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors(['name', 'color']);
        $this->assertEquals(0, Project::count());
    }

    public function test_users_can_view_their_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Мой личный проект',
            'description' => 'Подробное описание проекта',
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('Мой личный проект');
        $response->assertSee('Подробное описание проекта');
    }

    public function test_users_cannot_view_other_users_projects(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get(route('projects.show', $projectB));

        $response->assertStatus(403);
    }

    public function test_users_can_view_edit_form_for_their_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('projects.edit', $project));

        $response->assertStatus(200);
        $response->assertSee($project->name);
    }

    public function test_users_cannot_view_edit_form_for_other_users_projects(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->get(route('projects.edit', $projectB));

        $response->assertStatus(403);
    }

    public function test_users_can_update_their_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Старое название',
            'color' => '#3b82f6',
        ]);

        $response = $this->actingAs($user)->put(route('projects.update', $project), [
            'name' => 'Обновлённое название',
            'description' => 'Новое описание проекта',
            'color' => '#ef4444',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertEquals('Обновлённое название', $project->name);
        $this->assertEquals('Новое описание проекта', $project->description);
        $this->assertEquals('#ef4444', $project->color);
    }

    public function test_users_cannot_update_other_users_projects(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create([
            'user_id' => $userB->id,
            'name' => 'Название Б',
        ]);

        $response = $this->actingAs($userA)->put(route('projects.update', $projectB), [
            'name' => 'Взлом проекта',
            'color' => '#ef4444',
        ]);

        $response->assertStatus(403);

        $projectB->refresh();
        $this->assertEquals('Название Б', $projectB->name);
    }

    public function test_users_can_delete_their_own_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_users_cannot_delete_other_users_projects(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectB = Project::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA)->delete(route('projects.destroy', $projectB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $projectB->id]);
    }

    public function test_deleting_project_cascades_and_deletes_associated_tasks(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $task1 = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача проекта 1',
        ]);
        $task2 = Task::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Задача проекта 2',
        ]);

        $response = $this->actingAs($user)->delete(route('projects.destroy', $project));

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    }
}
