<?php

namespace App\Policies;

use App\Models\Major; // Import Model Major
use App\Models\User;  // Import Model User
use Illuminate\Auth\Access\HandlesAuthorization;

class MajorPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    public function view(User $user, Major $major): bool
    {
        return $user->role->name === 'Admin';
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    public function update(User $user, Major $major): bool
    {
        return $user->role->name === 'Admin';
    }

    public function delete(User $user, Major $major): bool
    {
        return $user->role->name === 'Admin';
    }

    // ...
}