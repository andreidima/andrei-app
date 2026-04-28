<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DatabaseController extends Controller
{
    public function index(Request $request)
    {
        $dbMigrations = collect();
        $repoMigrations = collect();
        $pendingMigrations = collect();
        $ranMigrations = collect();
        $dbOnlyMigrations = collect();
        $pretendOutput = '';
        $pretendError = null;

        if (Schema::hasTable('migrations')) {
            $dbMigrations = DB::table('migrations')
                ->orderBy('batch')
                ->orderBy('migration')
                ->get()
                ->map(fn ($migration) => [
                    'migration' => $migration->migration,
                    'batch' => $migration->batch,
                ]);
        }

        $migrationPaths = collect([
            database_path('migrations'),
            base_path('vendor/laravel/telescope/database/migrations'),
        ])->filter(fn ($path) => File::isDirectory($path));

        $repoMigrations = $migrationPaths
            ->flatMap(fn ($path) => File::files($path))
            ->map(fn ($file) => [
                'migration' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'filename' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'contents' => File::get($file->getRealPath()),
            ])
            ->sortBy('migration')
            ->values();

        $dbMigrationNames = $dbMigrations->pluck('migration');
        $repoMigrationNames = $repoMigrations->pluck('migration');

        $pendingMigrations = $repoMigrations
            ->filter(fn ($migration) => ! $dbMigrationNames->contains($migration['migration']))
            ->values();

        $ranMigrations = $repoMigrations
            ->map(function ($migration) use ($dbMigrations) {
                $dbMigration = $dbMigrations->firstWhere('migration', $migration['migration']);

                return [
                    ...$migration,
                    'batch' => $dbMigration['batch'] ?? null,
                ];
            })
            ->filter(fn ($migration) => $migration['batch'] !== null)
            ->values();

        $dbOnlyMigrations = $dbMigrations
            ->filter(fn ($migration) => ! $repoMigrationNames->contains($migration['migration']))
            ->values();

        try {
            Artisan::call('migrate', ['--pretend' => true]);
            $pretendOutput = trim(Artisan::output());
        } catch (Throwable $exception) {
            $pretendError = $exception->getMessage();
        }

        $tables = $this->getTables();
        $schemaDumpPath = database_path('schema/mysql-schema.sql');

        return view('system.database', [
            'databaseInfo' => [
                'connection' => config('database.default'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'app_env' => config('app.env'),
                'app_url' => config('app.url'),
            ],
            'tables' => $tables,
            'dbMigrations' => $dbMigrations,
            'repoMigrations' => $repoMigrations,
            'pendingMigrations' => $pendingMigrations,
            'ranMigrations' => $ranMigrations,
            'dbOnlyMigrations' => $dbOnlyMigrations,
            'pretendOutput' => $pretendOutput,
            'pretendError' => $pretendError,
            'schemaDumpExists' => File::exists($schemaDumpPath),
            'schemaDumpPath' => $schemaDumpPath,
            'schemaDumpSize' => File::exists($schemaDumpPath) ? File::size($schemaDumpPath) : null,
            'schemaDumpModifiedAt' => File::exists($schemaDumpPath) ? date('Y-m-d H:i:s', File::lastModified($schemaDumpPath)) : null,
            'lastMigrationOutput' => $request->session()->get('migration_output'),
        ]);
    }

    public function migrate(Request $request): RedirectResponse
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return back()->with([
                'status' => 'Migrarile pending au fost rulate.',
                'migration_output' => trim(Artisan::output()),
            ]);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    private function getTables(): Collection
    {
        $statusRows = collect(DB::select('SHOW TABLE STATUS'))
            ->map(fn ($row) => (array) $row)
            ->keyBy('Name');

        return $statusRows
            ->map(function ($table) {
                $name = $table['Name'];

                return [
                    'name' => $name,
                    'rows' => $table['Rows'],
                    'engine' => $table['Engine'],
                    'collation' => $table['Collation'],
                    'columns' => Schema::getColumnListing($name),
                ];
            })
            ->sortBy('name')
            ->values();
    }
}
