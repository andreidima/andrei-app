<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 32);
            $table->unsignedInteger('version_code');
            $table->string('version_name', 32);
            $table->text('apk_url')->nullable();
            $table->text('release_notes')->nullable();
            $table->boolean('required')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'version_code']);
            $table->index(['platform', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_releases');
    }
};
