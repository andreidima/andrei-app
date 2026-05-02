<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('mobile_app_releases')->updateOrInsert(
            [
                'platform' => 'android',
                'version_code' => 3,
            ],
            [
                'version_name' => '1.0.2',
                'apk_url' => 'https://expo.dev/accounts/andreidima/projects/andrei-app-mobile/builds/91040dfb-7fee-4308-a1f3-c9502cbf4071',
                'release_notes' => 'Am adaugat verificarea automata pentru actualizari Android.',
                'required' => false,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('mobile_app_releases')
            ->where('platform', 'android')
            ->where('version_code', 3)
            ->delete();
    }
};
