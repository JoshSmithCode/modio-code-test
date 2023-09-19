<?php

namespace App\Http\Middleware;

use App\Models\Mod;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModBelongsToGame extends Middleware
{
    public function handle($request, \Closure $next, ...$guards)
    {
        if (! $modId = $request->route()->parameter('mod')) {
            return $next($request);
        } elseif (! $gameId = $request->route()->parameter('game')) {
            return $next($request);
        }

        $mod = Mod::find($modId);

        if (! $mod || $mod->game_id != $gameId) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
