<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('mobile_app_releases')->insertOrIgnore([
            'platform' => 'android',
            'version_code' => 1,
            'version_name' => '1.0.0',
            'apk_url' => null,
            'release_notes' => null,
            'required' => false,
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('mobile_app_releases')
            ->where('platform', 'android')
            ->where('version_code', 1)
            ->delete();
    }
};
