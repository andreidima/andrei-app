@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                @if (session('status'))
                    <div class="alert alert-success shadow-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white culoare2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-database me-1"></i>Database & migrations</span>
                        <span class="badge bg-light text-dark">{{ $databaseInfo['app_env'] }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Connection</div>
                                    <div class="fw-bold">{{ $databaseInfo['connection'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Database</div>
                                    <div class="fw-bold">{{ $databaseInfo['database'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Host</div>
                                    <div class="fw-bold">{{ $databaseInfo['host'] }}:{{ $databaseInfo['port'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Tables</div>
                                    <div class="fw-bold">{{ $tables->count() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <div class="fw-bold mb-1">Important</div>
                            <div>
                                Migrations nu sterg date in mod automat, dar pot modifica sau elimina coloane si tabele daca acel cod exista in fisierele de migrari.
                                In acest proiect, baza pentru instalari noi este schema dump-ul din <code>database/schema/mysql-schema.sql</code>, iar migrarile noi se aplica peste el.
                                Verificati mereu sectiunea de pending migrations si SQL preview inainte de a rula migrarile.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white culoare2">
                        <i class="fa-solid fa-code-branch me-1"></i>Migration status
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Repo migrations</div>
                                    <div class="fw-bold">{{ $repoMigrations->count() }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Ran in database</div>
                                    <div class="fw-bold">{{ $ranMigrations->count() }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fw-bold">{{ $pendingMigrations->count() }}</div>
                                </div>
                            </div>
                        </div>

                        @if ($dbOnlyMigrations->count())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-1">Migration history mismatch</div>
                                <div class="mb-2">
                                    Aceste migrari sunt in tabela <code>migrations</code>, dar nu exista in caile de migrari ale proiectului.
                                    Asta inseamna ca istoricul repo nu reproduce complet baza de date.
                                </div>
                                <ul class="mb-0">
                                    @foreach ($dbOnlyMigrations as $migration)
                                        <li><code>{{ $migration['migration'] }}</code> (batch {{ $migration['batch'] }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($schemaDumpExists)
                            <div class="alert alert-success">
                                <div class="fw-bold mb-1">Schema baseline exists</div>
                                <div><code>{{ $schemaDumpPath }}</code></div>
                                <div>Updated: {{ $schemaDumpModifiedAt }} | Size: {{ number_format(($schemaDumpSize ?? 0) / 1024, 1) }} KB</div>
                                <div class="mt-1">
                                    Pentru un mediu nou si gol, Laravel poate porni de la acest fisier de schema, apoi aplica eventualele migrari mai noi.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Nu exista inca un schema dump in <code>database/schema/mysql-schema.sql</code>.
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <form method="POST" action="{{ route('system.database.backup') }}">
                                @csrf
                                <button class="btn btn-outline-primary rounded-3" type="submit">
                                    <i class="fa-solid fa-download me-1"></i>Download database backup
                                </button>
                            </form>
                            <form method="POST" action="{{ route('system.database.migrate') }}">
                                @csrf
                                <button class="btn btn-danger rounded-3" type="submit">
                                    <i class="fa-solid fa-play me-1"></i>Run pending migrations
                                </button>
                            </form>
                            <span class="align-self-center text-muted small">
                                Backup-urile sunt temporare si se sterg dupa download. Migrations creeaza mai intai un backup DB-only, apoi ruleaza <code>php artisan migrate --force</code>.
                            </span>
                        </div>

                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-bold mb-1">Backup location</div>
                            <div class="small text-muted mb-2">
                                Disk: <code>{{ $backupDisk }}</code> | Name: <code>{{ $backupName }}</code>
                            </div>
                            <div class="mb-3"><code>{{ $backupPath }}</code></div>
                            <div class="small text-muted mb-3">
                                Fisierele sunt pastrate aici doar temporar. Linkurile de download sterg arhiva dupa ce browserul o descarca.
                            </div>

                            @if ($recentBackups->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Recent backup</th>
                                                <th>Size</th>
                                                <th>Modified</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentBackups as $backup)
                                                <tr>
                                                    <td>
                                                        <code>{{ $backup['filename'] }}</code>
                                                        <div class="small text-muted"><code>{{ $backup['path'] }}</code></div>
                                                    </td>
                                                    <td>{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                                    <td>
                                                        {{ $backup['modified_at'] }}
                                                        <div>
                                                            <a href="{{ route('system.database.backups.download', ['filename' => $backup['filename']]) }}">Download si sterge</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted small">No backup ZIPs found yet.</div>
                            @endif
                        </div>

                        @if ($pendingMigrations->count())
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Pending migration</th>
                                            <th>File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingMigrations as $migration)
                                            <tr>
                                                <td><code>{{ $migration['migration'] }}</code></td>
                                                <td><code>{{ $migration['filename'] }}</code></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <details>
                                                        <summary class="fw-bold">Vezi codul migrarii</summary>
                                                        <pre class="bg-light border rounded-3 p-3 small mt-2 mb-0" style="white-space: pre-wrap">{{ $migration['contents'] }}</pre>
                                                    </details>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                Nu exista migrari pending in acest moment.
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="fw-bold mb-2">What pending migrations will do</div>
                            @if ($pretendError)
                                <div class="alert alert-danger mb-0">{{ $pretendError }}</div>
                            @elseif ($pretendOutput)
                                <pre class="bg-light border rounded-3 p-3 small mb-0" style="white-space: pre-wrap">{{ $pretendOutput }}</pre>
                            @else
                                <div class="border rounded-3 p-3 bg-light">No SQL preview available. Usually this means there is nothing to migrate.</div>
                            @endif
                        </div>

                        <div class="alert alert-secondary mb-0">
                            <div class="fw-bold mb-1">Recommended workflow</div>
                            <div class="mb-1">Existing environment with data: inspect pending migrations here, review the SQL preview, then run migrations.</div>
                            <div>Fresh empty environment: create the empty database, then run <code>php artisan migrate</code> so Laravel can use the schema baseline and apply newer migrations.</div>
                        </div>

                        @if ($lastMigrationOutput)
                            <div class="mt-3">
                                <div class="fw-bold mb-2">Last migrate output</div>
                                <pre class="bg-light border rounded-3 p-3 small mb-0" style="white-space: pre-wrap">{{ $lastMigrationOutput }}</pre>
                            </div>
                        @endif

                        @if (session('backup_output'))
                            <div class="mt-3">
                                <div class="fw-bold mb-2">Pre-migration backup output</div>
                                @if (session('backup_filename'))
                                    <div class="small text-muted mb-2">Filename: <code>{{ session('backup_filename') }}</code></div>
                                    <a class="btn btn-outline-primary btn-sm rounded-3 mb-2" href="{{ route('system.database.backups.download', ['filename' => session('backup_filename')]) }}">
                                        <i class="fa-solid fa-download me-1"></i>Download si sterge backup-ul pre-migration
                                    </a>
                                @endif
                                <pre class="bg-light border rounded-3 p-3 small mb-0" style="white-space: pre-wrap">{{ session('backup_output') }}</pre>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white culoare2">
                        <i class="fa-solid fa-table me-1"></i>Current tables
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="databaseTablesAccordion">
                            @foreach ($tables as $table)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $loop->index }}">
                                            <span class="me-3"><code>{{ $table['name'] }}</code></span>
                                            <span class="badge bg-secondary me-2">{{ $table['rows'] }} rows</span>
                                            <span class="badge bg-light text-dark me-2">{{ count($table['columns']) }} columns</span>
                                            <span class="text-muted small">{{ $table['engine'] }} | {{ $table['collation'] }}</span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#databaseTablesAccordion">
                                        <div class="accordion-body">
                                            <div class="small text-muted mb-2">Columns</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach ($table['columns'] as $column)
                                                    <span class="badge bg-light text-dark border">{{ $column }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
