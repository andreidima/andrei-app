<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActualizareController;
use App\Http\Controllers\Api\PontajController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/actualizari', [ActualizareController::class, 'index']);

    Route::prefix('pontaje')->group(function () {
        Route::get('/', [PontajController::class, 'index']);
        Route::get('/summary', [PontajController::class, 'summary']);
        Route::post('/start', [PontajController::class, 'start']);
        Route::post('/stop', [PontajController::class, 'stop']);
        Route::patch('/{pontaj}', [PontajController::class, 'update']);
        Route::delete('/{pontaj}', [PontajController::class, 'destroy']);
        Route::post('/{pontaj}/restart', [PontajController::class, 'restart']);
    });
});
