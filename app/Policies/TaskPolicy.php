<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Разрешить просмотр задачи только её владельцу.
     */
    public function view(User $user, Task $task): bool
    {
        return (int) $user->id === (int) $task->user_id;
    }

    /**
     * Разрешить обновление задачи (и смену статуса) только её владельцу.
     */
    public function update(User $user, Task $task): bool
    {
        return (int) $user->id === (int) $task->user_id;
    }

    /**
     * Разрешить удаление задачи только её владельцу.
     */
    public function delete(User $user, Task $task): bool
    {
        return (int) $user->id === (int) $task->user_id;
    }
}
