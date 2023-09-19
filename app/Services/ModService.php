<?php

namespace App\Services;

use App\Http\Resources\ModResource;
use App\Models\Game;
use App\Models\Mod;
use App\Models\User;

/**
 * ModService
 */
class ModService
{
    public function create(User $user, Game $game, string $name): ModResource
    {
        $mod = new Mod;
        $mod->game_id = $game->id;
        $mod->user_id = $user->id;
        $mod->name = $name;
        $mod->save();
        $mod->refresh();

        return new ModResource($mod);
    }

    public function update(Mod $mod, string $name): ModResource
    {
        $mod->name = $name;
        $mod->save();
        $mod->refresh();

        return new ModResource($mod);
    }
}
