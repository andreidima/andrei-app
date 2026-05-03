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
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
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
        $backupName = 'temporary-db-backups';
        $backupDisk = 'local';
        $backupPath = $this->backupDirectory();
        $recentBackups = $backupPath && File::isDirectory($backupPath)
            ? collect(File::files($backupPath))
                ->filter(fn ($file) => $file->getExtension() === 'sql')
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
            'lastComposerOutput' => $request->session()->get('composer_output'),
            'composerPharExists' => File::exists(base_path('composer.phar')),
            'composerPharPath' => base_path('composer.phar'),
            'composerPharSize' => File::exists(base_path('composer.phar')) ? File::size(base_path('composer.phar')) : null,
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
        abort_unless(str_ends_with($filename, '.sql'), 404);

        $backupPath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        abort_unless(File::exists($backupPath), 404);

        return response()
            ->download($backupPath, $filename)
            ->deleteFileAfterSend(true);
    }

    public function composerInstall(Request $request): RedirectResponse
    {
        $command = $this->composerBinary() . ' install --no-dev --optimize-autoloader --no-interaction';

        try {
            $output = $this->runShellCommand($command);

            Artisan::call('optimize:clear');
            $output .= "\n\n" . trim(Artisan::output());

            return back()->with([
                'status' => 'Composer install a fost rulat.',
                'composer_output' => trim($output),
            ]);
        } catch (Throwable $exception) {
            return back()->with([
                'error' => $exception->getMessage(),
                'composer_output' => trim($exception->getMessage()),
            ]);
        }
    }

    public function downloadComposer(Request $request): RedirectResponse
    {
        try {
            $composerUrl = 'https://getcomposer.org/download/latest-stable/composer.phar';
            $composerPath = base_path('composer.phar');
            $composerContents = @file_get_contents($composerUrl);

            if ($composerContents === false) {
                throw new RuntimeException('Nu am putut descarca composer.phar. Serverul poate bloca request-urile externe.');
            }

            if (file_put_contents($composerPath, $composerContents) === false) {
                throw new RuntimeException('Nu am putut salva composer.phar in radacina proiectului.');
            }

            return back()->with([
                'status' => 'composer.phar a fost descarcat.',
                'composer_output' => 'Downloaded ' . number_format(File::size($composerPath) / 1024 / 1024, 2) . ' MB to ' . $composerPath,
            ]);
        } catch (Throwable $exception) {
            return back()->with([
                'error' => $exception->getMessage(),
                'composer_output' => trim($exception->getMessage()),
            ]);
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

    private function createDatabaseBackup(string $prefix): array
    {
        $filename = $prefix . '-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        File::ensureDirectoryExists($this->backupDirectory());
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Nu am putut crea fisierul temporar de backup.');
        }

        try {
            $this->writeDatabaseSqlDump($handle);
        } finally {
            fclose($handle);
        }

        return [
            'filename' => $filename,
            'output' => 'Backup SQL creat: ' . $filename . ' (' . number_format(File::size($path) / 1024, 1) . ' KB)',
            'path' => $path,
        ];
    }

    private function backupDirectory(): string
    {
        return storage_path('app/temporary-db-backups');
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

    private function writeDatabaseSqlDump($handle): void
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $pdo = DB::connection()->getPdo();

        fwrite($handle, "-- Andrei App database backup\n");
        fwrite($handle, "-- Database: {$database}\n");
        fwrite($handle, "-- Created: " . now()->toDateTimeString() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->baseTables() as $table) {
            $quotedTable = $this->quoteIdentifier($table);
            $createRow = (array) DB::selectOne('SHOW CREATE TABLE ' . $quotedTable);
            $createSql = array_values($createRow)[1] ?? null;

            if (! $createSql) {
                continue;
            }

            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
            fwrite($handle, $createSql . ";\n\n");

            foreach (DB::table($table)->cursor() as $row) {
                $row = (array) $row;
                $columns = collect(array_keys($row))
                    ->map(fn ($column) => $this->quoteIdentifier($column))
                    ->implode(', ');
                $values = collect(array_values($row))
                    ->map(fn ($value) => $this->quoteValue($value, $pdo))
                    ->implode(', ');

                fwrite($handle, "INSERT INTO {$quotedTable} ({$columns}) VALUES ({$values});\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    private function baseTables(): Collection
    {
        return collect(DB::select('SHOW FULL TABLES'))
            ->map(fn ($row) => array_values((array) $row))
            ->filter(fn ($row) => ($row[1] ?? null) === 'BASE TABLE')
            ->map(fn ($row) => $row[0])
            ->values();
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function quoteValue(mixed $value, \PDO $pdo): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $pdo->quote((string) $value);
    }

    private function composerBinary(): string
    {
        $composerPhar = base_path('composer.phar');

        if (File::exists($composerPhar)) {
            return $this->phpCliBinary() . ' ' . escapeshellarg($composerPhar);
        }

        return 'composer';
    }

    private function phpCliBinary(): string
    {
        $configuredBinary = env('COMPOSER_PHP_BINARY');

        if ($configuredBinary) {
            return escapeshellcmd($configuredBinary);
        }

        foreach ($this->phpCliCandidates() as $candidate) {
            if ($this->isPhpCliBinary($candidate)) {
                return escapeshellcmd($candidate);
            }
        }

        throw new RuntimeException(
            'Nu am gasit un PHP CLI binary pentru Composer. Seteaza COMPOSER_PHP_BINARY in .env, de exemplu /usr/bin/php sau /usr/local/bin/php.'
        );
    }

    private function phpCliCandidates(): array
    {
        return array_values(array_unique(array_filter([
            'php',
            'php-cli',
            '/usr/bin/php',
            '/usr/local/bin/php',
            PHP_BINDIR ? PHP_BINDIR . DIRECTORY_SEPARATOR . 'php' : null,
            PHP_BINARY,
        ])));
    }

    private function isPhpCliBinary(string $candidate): bool
    {
        $command = escapeshellcmd($candidate) . ' -r "echo PHP_SAPI;"';

        try {
            $output = $this->runShellCommand($command);
        } catch (Throwable) {
            return false;
        }

        return trim($output) === 'cli';
    }

    private function runShellCommand(string $commandLine): string
    {
        if (class_exists(Process::class)) {
            $process = Process::fromShellCommandline($commandLine, base_path(), null, null, 300);
            $process->run();

            $output = trim($process->getOutput() . "\n" . $process->getErrorOutput());

            if (! $process->isSuccessful()) {
                throw new RuntimeException($output ?: 'Comanda Composer a esuat.');
            }

            return $output;
        }

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($commandLine, $descriptorSpec, $pipes, base_path());

        if (! is_resource($process)) {
            throw new RuntimeException('Nu am putut porni comanda Composer.');
        }

        $output = stream_get_contents($pipes[1]) . "\n" . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(trim($output) ?: 'Comanda Composer a esuat.');
        }

        return trim($output);
    }
}
