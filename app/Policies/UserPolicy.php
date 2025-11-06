<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $currentUser->is_admin;
    }

    public function destroy(User $currentUser, User $user)
    {
        // 管理员可以删除其他用户，但不能删除自己
        return $currentUser->is_admin && ($currentUser->id !== $user->id);
    }

}
