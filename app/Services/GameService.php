<?php

namespace App\Services;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;

/**
 * GameService
 */
class GameService
{
    public function create(User $user, string $name): GameResource
    {
        $game = new Game;
        $game->user_id = $user->id;
        $game->name = $name;
        $game->save();
        $game->refresh();

        return new GameResource($game);
    }

    public function update(Game $game, string $name): GameResource
    {
        $game->name = $name;
        $game->save();
        $game->refresh();

        return new GameResource($game);
    }
}
