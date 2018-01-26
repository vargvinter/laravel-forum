<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $user, User $signedInUser) // $user <- profileUser, $signedInUser <- auth()->user()
    {
        return $signedInUser->id === $user->id;
    }
}
