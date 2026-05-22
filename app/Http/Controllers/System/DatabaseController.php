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
        $destructivePendingMigrations = collect();
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

        $destructivePendingMigrations = $this->detectDestructiveMigrations($pendingMigrations);

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
            'destructivePendingMigrations' => $destructivePendingMigrations,
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
            $destructivePendingMigrations = $this->detectDestructiveMigrations($this->pendingMigrationFiles());

            if ($destructivePendingMigrations->isNotEmpty() && ! $request->boolean('confirm_destructive_migrations')) {
                return back()->with([
                    'error' => 'Exista migrari pending cu operatii potential distructive. Confirma explicit inainte de rulare.',
                ]);
            }

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
            ->deleteFileAfterSend(! str_starts_with($filename, 'pre-migrate-'));
    }

    public function testMysqlDump(): RedirectResponse
    {
        $testDumpPath = null;

        try {
            File::ensureDirectoryExists($this->backupDirectory());

            $connection = config('database.connections.' . config('database.default'));
            $database = $connection['database'] ?? null;
            $host = $connection['host'] ?? '127.0.0.1';
            $port = $connection['port'] ?? '3306';
            $username = $connection['username'] ?? null;
            $password = $connection['password'] ?? '';

            if (! $database || ! $username) {
                throw new RuntimeException('Nu am gasit database sau username in configuratia Laravel.');
            }

            $binary = $this->mysqlDumpBinary();
            $versionOutput = $this->runShellCommand($binary . ' --version');
            $testDumpPath = $this->backupDirectory() . DIRECTORY_SEPARATOR . 'mysqldump-test-' . now()->format('Y-m-d-H-i-s') . '.sql';

            $command = implode(' ', [
                $binary,
                '--host=' . escapeshellarg($host),
                '--port=' . escapeshellarg((string) $port),
                '--user=' . escapeshellarg($username),
                '--no-data',
                '--single-transaction',
                '--skip-lock-tables',
                '--result-file=' . escapeshellarg($testDumpPath),
                escapeshellarg($database),
            ]);

            $this->runShellCommand($command, $this->mysqlDumpEnvironment((string) $password));

            if (! File::exists($testDumpPath) || File::size($testDumpPath) === 0) {
                throw new RuntimeException('mysqldump a rulat, dar nu a creat un fisier SQL valid.');
            }

            $size = File::size($testDumpPath);
            File::delete($testDumpPath);

            return back()->with([
                'status' => 'mysqldump este disponibil si poate crea un dump schema-only cu credentialele curente.',
                'mysqldump_output' => trim($versionOutput . "\nSchema-only test dump creat si sters: " . number_format($size / 1024, 1) . ' KB'),
            ]);
        } catch (Throwable $exception) {
            if ($testDumpPath && File::exists($testDumpPath)) {
                File::delete($testDumpPath);
            }

            return back()->with([
                'error' => 'Testul mysqldump a esuat: ' . $exception->getMessage(),
                'mysqldump_output' => trim($exception->getMessage()),
            ]);
        }
    }

    public function composerInstall(Request $request): RedirectResponse
    {
        $command = $this->composerBinary() . ' install --no-dev --optimize-autoloader --no-interaction';

        try {
            $this->clearBootstrapCaches();
            $output = $this->runShellCommand($command, $this->composerEnvironment());

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

        try {
            return $this->createMysqlDumpBackup($filename, $path);
        } catch (Throwable $exception) {
            return $this->createPhpDatabaseBackup($filename, $path, $exception->getMessage());
        }
    }

    private function createMysqlDumpBackup(string $filename, string $path): array
    {
        $connection = config('database.connections.' . config('database.default'));
        $database = $connection['database'] ?? null;
        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? '3306';
        $username = $connection['username'] ?? null;
        $password = $connection['password'] ?? '';

        if (! $database || ! $username) {
            throw new RuntimeException('Nu am gasit database sau username in configuratia Laravel.');
        }

        $command = implode(' ', [
            $this->mysqlDumpBinary(),
            '--host=' . escapeshellarg($host),
            '--port=' . escapeshellarg((string) $port),
            '--user=' . escapeshellarg($username),
            '--single-transaction',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--events',
            '--result-file=' . escapeshellarg($path),
            escapeshellarg($database),
        ]);

        $this->runShellCommand($command, $this->mysqlDumpEnvironment((string) $password));

        if (! File::exists($path) || File::size($path) === 0) {
            throw new RuntimeException('mysqldump a rulat, dar nu a creat un fisier SQL valid.');
        }

        return [
            'filename' => $filename,
            'output' => 'Backup SQL creat cu mysqldump: ' . $filename . ' (' . number_format(File::size($path) / 1024, 1) . ' KB)',
            'path' => $path,
            'driver' => 'mysqldump',
        ];
    }

    private function createPhpDatabaseBackup(string $filename, string $path, string $fallbackReason): array
    {
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
            'output' => 'Backup SQL creat cu fallback PHP: ' . $filename . ' (' . number_format(File::size($path) / 1024, 1) . " KB)\nMotiv fallback mysqldump: " . $fallbackReason,
            'path' => $path,
            'driver' => 'php',
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
            ->filter(fn ($file) => $file->getMTime() < now()->subDays(14)->getTimestamp())
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

    private function mysqlDumpBinary(): string
    {
        return escapeshellcmd(env('MYSQLDUMP_BINARY', 'mysqldump'));
    }

    private function clearBootstrapCaches(): void
    {
        collect([
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes-v7.php'),
            base_path('bootstrap/cache/events.php'),
        ])
            ->filter(fn ($path) => File::exists($path))
            ->each(fn ($path) => File::delete($path));
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
            $output = $this->runShellCommand($command, $this->composerEnvironment());
        } catch (Throwable) {
            return false;
        }

        return trim($output) === 'cli';
    }

    private function composerEnvironment(): array
    {
        $composerHome = storage_path('app/composer-home');
        $composerCache = storage_path('app/composer-cache');

        File::ensureDirectoryExists($composerHome);
        File::ensureDirectoryExists($composerCache);

        return [
            ...$_ENV,
            ...$_SERVER,
            'HOME' => $composerHome,
            'COMPOSER_HOME' => $composerHome,
            'COMPOSER_CACHE_DIR' => $composerCache,
        ];
    }

    private function mysqlDumpEnvironment(string $password): array
    {
        $environment = [
            ...$_ENV,
            ...$_SERVER,
        ];

        if ($password !== '') {
            $environment['MYSQL_PWD'] = $password;
        }

        return $environment;
    }

    private function pendingMigrationFiles(): Collection
    {
        if (! Schema::hasTable('migrations')) {
            return $this->repoMigrationFiles();
        }

        $dbMigrationNames = DB::table('migrations')->pluck('migration');

        return $this->repoMigrationFiles()
            ->filter(fn ($migration) => ! $dbMigrationNames->contains($migration['migration']))
            ->values();
    }

    private function repoMigrationFiles(): Collection
    {
        $migrationPaths = collect([
            database_path('migrations'),
            base_path('vendor/laravel/telescope/database/migrations'),
        ])->filter(fn ($path) => File::isDirectory($path));

        return $migrationPaths
            ->flatMap(fn ($path) => File::files($path))
            ->map(fn ($file) => [
                'migration' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'filename' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'contents' => File::get($file->getRealPath()),
            ])
            ->sortBy('migration')
            ->values();
    }

    private function detectDestructiveMigrations(Collection $migrations): Collection
    {
        $patterns = [
            'dropTable' => '/->\s*dropTable\s*\(|Schema::\s*drop\s*\(|Schema::\s*dropIfExists\s*\(/i',
            'dropColumn' => '/->\s*dropColumn\s*\(/i',
            'renameTable' => '/->\s*rename\s*\(|Schema::\s*rename\s*\(/i',
            'renameColumn' => '/->\s*renameColumn\s*\(/i',
            'raw DROP' => '/DB::\s*statement\s*\([^;]*(DROP\s+TABLE|DROP\s+COLUMN|ALTER\s+TABLE[^;]*\sDROP\s|TRUNCATE\s+TABLE)/is',
            'raw RENAME' => '/DB::\s*statement\s*\([^;]*(RENAME\s+TABLE|ALTER\s+TABLE[^;]*\sRENAME\s)/is',
        ];

        return $migrations
            ->map(function ($migration) use ($patterns) {
                $matches = collect($patterns)
                    ->filter(fn ($pattern) => preg_match($pattern, $migration['contents']))
                    ->keys()
                    ->values();

                return [
                    ...$migration,
                    'destructive_matches' => $matches,
                ];
            })
            ->filter(fn ($migration) => $migration['destructive_matches']->isNotEmpty())
            ->values();
    }

    private function runShellCommand(string $commandLine, ?array $env = null): string
    {
        if (class_exists(Process::class)) {
            $process = Process::fromShellCommandline($commandLine, base_path(), $env, null, 300);
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
        $process = proc_open($commandLine, $descriptorSpec, $pipes, base_path(), $env);

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
