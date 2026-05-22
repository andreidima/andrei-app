<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartamente', function (Blueprint $table) {
            $table->unsignedInteger('cheltuieli_lunare')->nullable()->after('pret');
            $table->unsignedInteger('costuri_extra_estimate')->nullable()->after('cheltuieli_lunare');
            $table->string('peb', 20)->nullable()->after('etaj');
            $table->boolean('are_lift')->nullable()->after('peb');
            $table->boolean('are_balcon')->nullable()->after('are_lift');
            $table->boolean('are_parcare')->nullable()->after('are_balcon');
            $table->string('orientare_lumina')->nullable()->after('are_parcare');
            $table->string('renovare_necesara')->nullable()->after('orientare_lumina');
            $table->string('zgomot')->nullable()->after('renovare_necesara');
            $table->string('zona')->nullable()->after('zgomot');
        });
    }

    public function down(): void
    {
        Schema::table('apartamente', function (Blueprint $table) {
            $table->dropColumn([
                'cheltuieli_lunare',
                'costuri_extra_estimate',
                'peb',
                'are_lift',
                'are_balcon',
                'are_parcare',
                'orientare_lumina',
                'renovare_necesara',
                'zgomot',
                'zona',
            ]);
        });
    }
};
