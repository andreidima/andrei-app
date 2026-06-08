<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Apps\AplicatieController;
use App\Http\Controllers\Apps\ActualizareController;
use App\Http\Controllers\Apps\PontajController;
use App\Http\Controllers\Apps\FacturaController;
use App\Http\Controllers\Apps\FeatureController;
use App\Http\Controllers\ApartamentController;
use App\Http\Controllers\System\DatabaseController;
use App\Http\Controllers\System\UserController;
use App\Http\Controllers\RefrainController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\Wardrobe\ClothingItemController;
use App\Http\Controllers\Wardrobe\MeetingController;
use App\Http\Controllers\Wardrobe\PersonController;
use App\Http\Controllers\ValidSoftwareBlog\BlogArticleController;
use App\Http\Controllers\ValidSoftwareBlog\BlogProjectController;

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
    Route::get('/acasa', function () {
        return auth()->user()?->isAdmin()
            ? view('acasa')
            : redirect()->route('apartamente.index');
    })->name('acasa');

    // Group all "apps" routes under the /apps prefix and name prefix "apps."
    Route::group(['prefix' => 'apps', 'as' => 'apps.', 'middleware' => 'can:access-admin-area'], function () {
        // Aplicatii resource routes
        Route::resource('aplicatii', AplicatieController::class)
            ->parameters(['aplicatii' => 'aplicatie']);

        // Features routes
        Route::post('features/{feature}/implementations', [FeatureController::class, 'saveImplementations'])
            ->name('features.implementations.save');
        Route::resource('features', FeatureController::class)
            ->parameters(['features' => 'feature']);

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
        Route::get('pontaje/statistica-grafice', [PontajController::class, 'statisticaGrafice'])
            ->name('pontaje.statistica_grafice');
        Route::resource('pontaje', PontajController::class)
            ->parameters(['pontaje' => 'pontaj']);

        // Facturi routes
        Route::get('facturi/{factura}/export', [FacturaController::class, 'export'])
            ->name('facturi.export');
        Route::resource('facturi', FacturaController::class)
            ->parameters(['facturi' => 'factura']);

    });

    // Apartamente routes
    Route::get('apartamente/calendar', [ApartamentController::class, 'calendar'])
        ->name('apartamente.calendar')
        ->middleware('can:access-apartments');

    Route::resource('apartamente', ApartamentController::class)
        ->parameters(['apartamente' => 'apartament'])
        ->middleware('can:access-apartments');

    // Notificari view route (outside of the /apps prefix)
    Route::view('/notificari', 'notificari.index')->name('notificari.index')->middleware('can:access-admin-area');

    Route::group(['middleware' => 'can:access-admin-area'], function () {
        Route::get('/system/database', [DatabaseController::class, 'index'])->name('system.database');
        Route::post('/system/database/backup', [DatabaseController::class, 'backup'])->name('system.database.backup');
        Route::get('/system/database/backups/{filename}', [DatabaseController::class, 'downloadBackup'])->name('system.database.backups.download');
        Route::post('/system/database/test-mysqldump', [DatabaseController::class, 'testMysqlDump'])->name('system.database.test_mysqldump');
        Route::post('/system/database/migrate', [DatabaseController::class, 'migrate'])->name('system.database.migrate');
        Route::post('/system/database/composer-download', [DatabaseController::class, 'downloadComposer'])->name('system.database.composer_download');
        Route::post('/system/database/composer-install', [DatabaseController::class, 'composerInstall'])->name('system.database.composer_install');
        Route::resource('/system/users', UserController::class)
            ->parameters(['users' => 'user'])
            ->names('system.users');
    });

    // Refrains routes
    Route::resource('refrains', RefrainController::class)->middleware('can:access-admin-area');

    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index')->middleware('can:access-admin-area');

    Route::group(['prefix' => 'articole-validsoftware', 'as' => 'validsoftware-blog.', 'middleware' => 'can:access-admin-area'], function () {
        Route::get('/', [BlogArticleController::class, 'index'])->name('index');
        Route::resource('proiecte', BlogProjectController::class)
            ->parameters(['proiecte' => 'project'])
            ->names('projects');
        Route::resource('articole', BlogArticleController::class)
            ->parameters(['articole' => 'article'])
            ->except(['index'])
            ->names('articles');
    });

    Route::get('/storage/wardrobe/{path}', function (string $path) {
        $storagePath = 'wardrobe/' . $path;

        abort_unless(\Illuminate\Support\Facades\Storage::disk('public')->exists($storagePath), 404);

        return \Illuminate\Support\Facades\Storage::disk('public')->response($storagePath);
    })->where('path', '.*')->name('wardrobe.storage')->middleware('can:access-admin-area');

    Route::group(['prefix' => 'wardrobe', 'as' => 'wardrobe.', 'middleware' => 'can:access-admin-area'], function () {
        Route::redirect('/', '/wardrobe/meetings')->name('index');
        Route::resource('people', PersonController::class);
        Route::resource('clothing-items', ClothingItemController::class)
            ->parameters(['clothing-items' => 'clothingItem']);
        Route::resource('meetings', MeetingController::class);
    });
});
