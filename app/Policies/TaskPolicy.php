<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;
class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

}
