<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_root_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_from_root_to_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('tasks.index'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Вход в аккаунт');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Создать аккаунт');
    }

    public function test_users_can_register_and_are_automatically_authenticated(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'name' => 'Иван Петров',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');
    }

    public function test_registration_validation_fails_with_invalid_data(): void
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Другой Пользователь',
            'email' => 'duplicate@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'alex@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'alex@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('tasks.index'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'alex@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'alex@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_throttling_triggers_after_multiple_failed_attempts(): void
    {
        RateLimiter::clear('alex@example.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'alex@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => 'alex@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('Слишком много неудачных попыток входа', $errors[0]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    public function test_unauthenticated_users_cannot_access_tasks(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_cannot_access_projects(): void
    {
        $response = $this->get(route('projects.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_tasks_and_projects(): void
    {
        $user = User::factory()->create();

        $tasksResponse = $this->actingAs($user)->get(route('tasks.index'));
        $tasksResponse->assertStatus(200);
        $tasksResponse->assertSee('Все задачи');

        $projectsResponse = $this->actingAs($user)->get(route('projects.index'));
        $projectsResponse->assertStatus(200);
        $projectsResponse->assertSee('Мои проекты');
    }
}
