<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Разрешить просмотр только владельцу проекта.
     */
    public function view(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    /**
     * Разрешить обновление только владельцу проекта.
     */
    public function update(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }

    /**
     * Разрешить удаление только владельцу проекта.
     */
    public function delete(User $user, Project $project): bool
    {
        return (int) $user->id === (int) $project->user_id;
    }
}
