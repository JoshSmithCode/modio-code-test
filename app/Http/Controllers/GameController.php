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
        $this->gameService = $gameService;
    }

    public function browse(Request $request): JsonResponse
    {
        return new JsonResponse(Game::paginate(10));
    }

    public function create(Request $request): JsonResponse
    {
        $gameResource = $this->gameService->create(
            $request->user(),
            $request->get('name')
        );

        return new JsonResponse($gameResource, Response::HTTP_CREATED);
    }

    public function read(Request $request, Game $game): JsonResponse
    {
        return new JsonResponse(new GameResource($game));
    }

    public function update(Request $request, Game $game): JsonResponse
    {
        if ($request->user()->cannot('update', $game)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $gameResource = $this->gameService->update($game, $request->get('name'));

        return new JsonResponse($gameResource);
    }

    public function delete(Request $request, Game $game): JsonResponse
    {
        if ($request->user()->cannot('delete', $game)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $game->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
