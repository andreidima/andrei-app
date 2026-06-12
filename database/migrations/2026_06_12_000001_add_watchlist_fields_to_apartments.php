<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartamente', function (Blueprint $table) {
            if (! Schema::hasColumn('apartamente', 'adaugat_in_lista_at')) {
                $table->dateTime('adaugat_in_lista_at')->nullable()->after('vizionare_at');
            }

            if (! Schema::hasColumn('apartamente', 'pret_initial')) {
                $table->unsignedInteger('pret_initial')->nullable()->after('pret');
            }

            if (! Schema::hasColumn('apartamente', 'pret_curent')) {
                $table->unsignedInteger('pret_curent')->nullable()->after('pret_initial');
            }

            if (! Schema::hasColumn('apartamente', 'ultima_verificare_at')) {
                $table->dateTime('ultima_verificare_at')->nullable()->after('pret_curent');
            }

            if (! Schema::hasColumn('apartamente', 'status_anunt')) {
                $table->string('status_anunt', 100)->nullable()->after('ultima_verificare_at');
            }

            if (! Schema::hasColumn('apartamente', 'observatii_status_anunt')) {
                $table->text('observatii_status_anunt')->nullable()->after('status_anunt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apartamente', function (Blueprint $table) {
            $columns = [
                'adaugat_in_lista_at',
                'pret_initial',
                'pret_curent',
                'ultima_verificare_at',
                'status_anunt',
                'observatii_status_anunt',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('apartamente', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
