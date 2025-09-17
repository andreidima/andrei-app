<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Apps\AplicatieController;
use App\Http\Controllers\Apps\ActualizareController;
use App\Http\Controllers\Apps\PontajController;
use App\Http\Controllers\Apps\FacturaController;
use App\Http\Controllers\RefrainController;
use App\Http\Controllers\AchievementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will be
| assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes([
    'register'        => false,
    'password.request'=> false,
    'reset'           => false,
]);

Route::redirect('/', '/acasa');

Route::group(['middleware' => 'auth'], function () {
    // Home page route
    Route::view('/acasa', 'acasa')->name('acasa');

    // Group all "apps" routes under the /apps prefix and name prefix "apps."
    Route::group(['prefix' => 'apps', 'as' => 'apps.'], function () {
        // Aplicatii resource routes
        Route::resource('aplicatii', AplicatieController::class)
            ->parameters(['aplicatii' => 'aplicatie']);

        // Actualizari routes
        Route::get('actualizari/axios', [ActualizareController::class, 'axios'])
            ->name('actualizari.axios');
        Route::resource('actualizari', ActualizareController::class)
            ->parameters(['actualizari' => 'actualizare']);

        // Pontaje routes
        Route::get('pontaje/{actualizare}/deschide-nou', [PontajController::class, 'deschideNou'])
            ->name('pontaje.deschide_nou');
        Route::get('pontaje/inchide', [PontajController::class, 'inchide'])
            ->name('pontaje.inchide');
        // Restricting to POST for resource addition
        Route::post('pontaje/adauga-resursa/{resursa}', [PontajController::class, 'adaugaResursa'])
            ->name('pontaje.adauga_resursa');
        Route::get('pontaje/statistica', [PontajController::class, 'statistica'])
            ->name('pontaje.statistica');
        Route::resource('pontaje', PontajController::class)
            ->parameters(['pontaje' => 'pontaj']);

        // Facturi routes
        Route::get('facturi/{factura}/export', [FacturaController::class, 'export'])
            ->name('facturi.export');
        Route::resource('facturi', FacturaController::class)
            ->parameters(['facturi' => 'factura']);

    });

    // Notificari view route (outside of the /apps prefix)
    Route::view('/notificari', 'notificari.index')->name('notificari.index');

    // Refrains routes
    Route::resources(['refrains' => RefrainController::class,]);

    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
});
