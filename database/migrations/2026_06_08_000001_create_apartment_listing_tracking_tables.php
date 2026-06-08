<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::create('apartment_search_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_search_id')->constrained('apartment_searches')->cascadeOnDelete();
            $table->timestamp('observed_at');
            $table->string('source_type', 50)->default('manual');
            $table->unsignedInteger('observed_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('apartment_listing_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_search_run_id')->constrained('apartment_search_runs')->cascadeOnDelete();
            $table->foreignId('external_apartment_listing_id')->constrained('external_apartment_listings')->cascadeOnDelete();
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

        Schema::create('apartment_listing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_search_id')->nullable()->constrained('apartment_searches')->nullOnDelete();
            $table->foreignId('apartment_search_run_id')->nullable()->constrained('apartment_search_runs')->nullOnDelete();
            $table->foreignId('external_apartment_listing_id')->constrained('external_apartment_listings')->cascadeOnDelete();
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

    public function down(): void
    {
        Schema::dropIfExists('apartment_listing_events');
        Schema::dropIfExists('apartment_listing_snapshots');
        Schema::dropIfExists('apartment_search_runs');
        Schema::dropIfExists('external_apartment_listings');
        Schema::dropIfExists('apartment_searches');
    }
};
