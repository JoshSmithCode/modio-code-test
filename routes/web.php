<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ModController;
use App\Http\Middleware\ModBelongsToGame;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'games', 'controller' => GameController::class], function () {

    Route::get('', 'browse');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('', 'create');
        Route::get('/{game}', 'read');
        Route::put('/{game}', 'update');
        Route::delete('/{game}', 'delete');
    });

    Route::group(['prefix' => '{game}/mods', 'controller' => ModController::class], function () {

        Route::get('', 'browse');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('', 'create');

            Route::middleware(ModBelongsToGame::class)->group(function () {
                Route::get('/{mod}', 'read');
                Route::put('/{mod}', 'update');
                Route::delete('/{mod}', 'delete');
            });
        });
    });
});
