<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('apartment_searches')) {
            Schema::create('apartment_searches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('source', 50)->default('manual');
                $table->string('url', 500)->nullable();
                $table->string('neighborhood')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_checked_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('external_apartment_listings')) {
            Schema::create('external_apartment_listings', function (Blueprint $table) {
                $table->id();
                $table->string('source', 50)->default('manual');
                $table->string('external_id')->nullable();
                $table->string('url', 500)->nullable();
                $table->string('title')->nullable();
                $table->string('locality')->nullable();
                $table->string('agency')->nullable();
                $table->unsignedInteger('latest_price')->nullable();
                $table->unsignedTinyInteger('latest_bedrooms')->nullable();
                $table->unsignedSmallInteger('latest_surface')->nullable();
                $table->string('latest_status', 100)->nullable();
                $table->boolean('latest_under_option')->nullable();
                $table->timestamp('first_seen_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->unique(['source', 'external_id']);
                $table->index('url');
            });
        }

        if (! Schema::hasTable('apartment_search_runs')) {
            Schema::create('apartment_search_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('apartment_search_id');
                $table->timestamp('observed_at');
                $table->string('source_type', 50)->default('manual');
                $table->unsignedInteger('observed_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('apartment_listing_snapshots')) {
            Schema::create('apartment_listing_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('apartment_search_run_id');
                $table->foreignId('external_apartment_listing_id');
                $table->string('title')->nullable();
                $table->string('locality')->nullable();
                $table->string('agency')->nullable();
                $table->unsignedInteger('price')->nullable();
                $table->unsignedTinyInteger('bedrooms')->nullable();
                $table->unsignedSmallInteger('surface')->nullable();
                $table->string('status', 100)->nullable();
                $table->boolean('under_option')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();

                $table->unique(['apartment_search_run_id', 'external_apartment_listing_id'], 'listing_snapshot_run_listing_unique');
            });
        }

        if (! Schema::hasTable('apartment_listing_events')) {
            Schema::create('apartment_listing_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('apartment_search_id')->nullable();
                $table->foreignId('apartment_search_run_id')->nullable();
                $table->foreignId('external_apartment_listing_id');
                $table->string('type', 50);
                $table->timestamp('occurred_at');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_reviewed')->default(false);
                $table->timestamps();

                $table->index(['type', 'is_reviewed']);
            });
        }

        $this->addForeignKeyIfMissing('apartment_search_runs', 'apartment_search_id', 'apartment_searches', 'apt_runs_search_fk', 'cascade');
        $this->addForeignKeyIfMissing('apartment_listing_snapshots', 'apartment_search_run_id', 'apartment_search_runs', 'apt_snapshots_run_fk', 'cascade');
        $this->addForeignKeyIfMissing('apartment_listing_snapshots', 'external_apartment_listing_id', 'external_apartment_listings', 'apt_snapshots_listing_fk', 'cascade');
        $this->addForeignKeyIfMissing('apartment_listing_events', 'apartment_search_id', 'apartment_searches', 'apt_events_search_fk', 'set null');
        $this->addForeignKeyIfMissing('apartment_listing_events', 'apartment_search_run_id', 'apartment_search_runs', 'apt_events_run_fk', 'set null');
        $this->addForeignKeyIfMissing('apartment_listing_events', 'external_apartment_listing_id', 'external_apartment_listings', 'apt_events_listing_fk', 'cascade');
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_listing_events');
        Schema::dropIfExists('apartment_listing_snapshots');
        Schema::dropIfExists('apartment_search_runs');
        Schema::dropIfExists('external_apartment_listings');
        Schema::dropIfExists('apartment_searches');
    }

    private function addForeignKeyIfMissing(string $table, string $column, string $referencedTable, string $constraintName, string $onDelete): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || $this->foreignKeyOnColumnExists($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column, $referencedTable, $constraintName, $onDelete) {
            $foreign = $table->foreign($column, $constraintName)->references('id')->on($referencedTable);

            if ($onDelete === 'cascade') {
                $foreign->cascadeOnDelete();
            }

            if ($onDelete === 'set null') {
                $foreign->nullOnDelete();
            }
        });
    }

    private function foreignKeyOnColumnExists(string $table, string $column): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};
