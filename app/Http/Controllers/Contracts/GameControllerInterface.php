<?php

namespace App\Http\Controllers\Contracts;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GameControllerInterface.
 */
interface GameControllerInterface
{
    /**
     * Browse Games.
     */
    public function browse(Request $request): JsonResponse;

    /**
     * Create game.
     */
    public function create(Request $request): JsonResponse;

    /**
     * Read/view a game.
     */
    public function read(Request $request, Game $game): JsonResponse;

    /**
     * Update a game.
     */
    public function update(Request $request, Game $game): JsonResponse;

    /**
     * Delete a game.
     */
    public function delete(Request $request, Game $game): JsonResponse;
}
