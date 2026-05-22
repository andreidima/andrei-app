<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apps_apartamente') && ! Schema::hasTable('apartamente')) {
            Schema::rename('apps_apartamente', 'apartamente');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('apartamente') && ! Schema::hasTable('apps_apartamente')) {
            Schema::rename('apartamente', 'apps_apartamente');
        }
    }
};
