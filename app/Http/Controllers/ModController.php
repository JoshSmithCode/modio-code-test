<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contracts\ModControllerInterface;
use App\Http\Resources\ModResource;
use App\Models\Game;
use App\Models\Mod;
use App\Services\ModService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModController implements ModControllerInterface
{
    private $modService;

    public function __construct(ModService $modService)
    {
        $this->modService = $modService;
    }

    public function browse(Request $request, Game $game): JsonResponse
    {
        return new JsonResponse(Mod::paginate(10));
    }

    /**
     * Create a mod.
     *
     * @return JsonResponse
     */
    public function create(Request $request, Game $game)
    {
        $modResource = $this->modService->create(
            $request->user(),
            $game,
            $request->get('name')
        );

        return new JsonResponse($modResource, Response::HTTP_CREATED);
    }

    public function read(Request $request, Game $game, Mod $mod): JsonResponse
    {
        return new JsonResponse(new ModResource($mod));
    }

    public function update(Request $request, Game $game, Mod $mod): JsonResponse
    {
        if ($request->user()->cannot('update', $mod)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $modResource = $this->modService->update($mod, $request->get('name'));

        return new JsonResponse($modResource);
    }

    public function delete(Request $request, Game $game, Mod $mod): JsonResponse
    {
        if ($request->user()->cannot('delete', $mod)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $mod->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
