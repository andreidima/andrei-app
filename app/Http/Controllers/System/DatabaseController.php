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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $backupName = config('backup.backup.name');
        $backupDisk = config('backup.backup.destination.disks.0', 'local');
        $backupDiskRoot = config("filesystems.disks.{$backupDisk}.root");
        $backupPath = $backupDiskRoot ? $backupDiskRoot . DIRECTORY_SEPARATOR . $backupName : null;
        $recentBackups = $backupPath && File::isDirectory($backupPath)
            ? collect(File::files($backupPath))
                ->filter(fn ($file) => $file->getExtension() === 'zip')
                ->sortByDesc(fn ($file) => $file->getMTime())
                ->take(10)
                ->map(fn ($file) => [
                    'filename' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => $file->getSize(),
                    'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ])
                ->values()
            : collect();

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
            'backupName' => $backupName,
            'backupDisk' => $backupDisk,
            'backupPath' => $backupPath,
            'recentBackups' => $recentBackups,
            'lastMigrationOutput' => $request->session()->get('migration_output'),
        ]);
    }

    public function migrate(Request $request): RedirectResponse
    {
        try {
            $this->cleanupOldTemporaryBackups();
            $backup = $this->createDatabaseBackup('pre-migrate');

            Artisan::call('migrate', ['--force' => true]);
            $migrationOutput = trim(Artisan::output());

            return back()->with([
                'status' => 'Backup-ul bazei de date a fost creat, apoi migrarile pending au fost rulate.',
                'migration_output' => $migrationOutput,
                'backup_output' => $backup['output'],
                'backup_filename' => $backup['filename'],
            ]);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function backup(): RedirectResponse
    {
        try {
            $this->cleanupOldTemporaryBackups();
            $backup = $this->createDatabaseBackup('manual-db');

            return redirect()
                ->route('system.database.backups.download', ['filename' => $backup['filename']])
                ->with('status', 'Backup-ul bazei de date a fost creat pentru download.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        abort_unless($filename === basename($filename), 404);
        abort_unless(str_ends_with($filename, '.zip'), 404);

        $backupPath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        abort_unless(File::exists($backupPath), 404);

        return response()
            ->download($backupPath, $filename)
            ->deleteFileAfterSend(true);
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

    private function createDatabaseBackup(string $prefix): array
    {
        $filename = $prefix . '-' . now()->format('Y-m-d-H-i-s') . '.zip';

        Artisan::call('backup:run', [
            '--only-db' => true,
            '--disable-notifications' => true,
            '--filename' => $filename,
        ]);

        return [
            'filename' => $filename,
            'output' => trim(Artisan::output()),
            'path' => $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename,
        ];
    }

    private function backupDirectory(): string
    {
        $backupName = config('backup.backup.name');
        $backupDisk = config('backup.backup.destination.disks.0', 'local');
        $backupDiskRoot = config("filesystems.disks.{$backupDisk}.root");

        return $backupDiskRoot . DIRECTORY_SEPARATOR . $backupName;
    }

    private function cleanupOldTemporaryBackups(): void
    {
        $backupPath = $this->backupDirectory();

        if (! File::isDirectory($backupPath)) {
            return;
        }

        collect(File::files($backupPath))
            ->filter(fn ($file) => in_array(true, [
                str_starts_with($file->getFilename(), 'pre-migrate-'),
                str_starts_with($file->getFilename(), 'manual-db-'),
            ], true))
            ->filter(fn ($file) => $file->getMTime() < now()->subDay()->getTimestamp())
            ->each(fn ($file) => File::delete($file->getRealPath()));
    }
}
