<?php

namespace App\Http\Controllers\Contracts;

use App\Models\Game;
use App\Models\Mod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GameControllerInterface.
 */
interface ModControllerInterface
{
    /**
     * Browse Mods.
     */
    public function browse(Request $request, Game $game): JsonResponse;

    /**
     * Create a mod.
     *
     * @return JsonResponse
     */
    public function create(Request $request, Game $game);

    /**
     * Read/view a mod.
     */
    public function read(Request $request, Game $game, Mod $mod): JsonResponse;

    /**
     * Update a mod.
     */
    public function update(Request $request, Game $game, Mod $mod): JsonResponse;

    /**
     * Delete a mod.
     */
    public function delete(Request $request, Game $game, Mod $mod): JsonResponse;
}
