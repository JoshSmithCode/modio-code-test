<?php

namespace App\Policies;

use App\Models\Mod;
use App\Models\User;

class ModPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Mod $mod): bool
    {
        return
            $user->id == $mod->user_id ||
            $user->id == $mod->game->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mod $mod): bool
    {
        return
            $user->id == $mod->user_id ||
            $user->id == $mod->game->user_id;
    }
}
