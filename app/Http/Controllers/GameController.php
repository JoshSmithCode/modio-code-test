<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contracts\GameControllerInterface;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GameController extends Controller implements GameControllerInterface
{
    private GameService $gameService;

    public function __construct(GameService $gameService)
    {
    }

    public function browse(Request $request): JsonResponse
    {
        return new JsonResponse(Game::paginate(10));
    }

    public function create(Request $request): JsonResponse
    {
        $game = $request->user()->games()->save(
            new Game(['name' => $request->get('name')])
        );

        return new JsonResponse(new GameResource($game), Response::HTTP_CREATED);
    }

    public function read(Request $request, Game $game): JsonResponse
    {
        return new JsonResponse(new GameResource($game));
    }

    public function update(Request $request, Game $game): JsonResponse
    {
        $game->name = $request->get('name');
        $game->save();
        $game->refresh();

        return new JsonResponse(new GameResource($game));
    }

    public function delete(Request $request, Game $game): JsonResponse
    {
        $game->delete();

        return new JsonResponse(null, 204);
    }
}
