<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps_apartamente', function (Blueprint $table) {
            $table->increments('id');
            $table->string('adresa');
            $table->string('localitate', 100)->nullable();
            $table->string('status', 50)->default('de_vazut');
            $table->dateTime('vizionare_at')->nullable();
            $table->unsignedInteger('pret')->nullable();
            $table->unsignedSmallInteger('suprafata_mp')->nullable();
            $table->unsignedTinyInteger('camere')->nullable();
            $table->smallInteger('etaj')->nullable();
            $table->string('link_anunt', 500)->nullable();
            $table->string('agentie')->nullable();
            $table->string('contact')->nullable();
            $table->text('puncte_bune')->nullable();
            $table->text('puncte_slabe')->nullable();
            $table->text('riscuri_intrebari')->nullable();
            $table->text('observatii')->nullable();
            $table->unsignedTinyInteger('scor')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'vizionare_at' => null,
            'suprafata_mp' => null,
            'camere' => null,
            'etaj' => null,
            'link_anunt' => null,
            'agentie' => null,
            'contact' => null,
            'puncte_bune' => null,
            'puncte_slabe' => null,
            'riscuri_intrebari' => null,
            'scor' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('apps_apartamente')->insert([
            array_merge($defaults, [
                'adresa' => 'Rue du Commerce 76',
                'localitate' => '1000 Bruxelles',
                'status' => 'vazut',
                'pret' => 529000,
                'puncte_slabe' => 'Lipsa lumina.',
                'observatii' => 'Apartament deja vizitat.',
            ]),
            array_merge($defaults, [
                'adresa' => 'Rue Froissart 135',
                'localitate' => 'Etterbeek',
                'status' => 'programat',
                'vizionare_at' => '2026-05-26 17:40:00',
                'pret' => 425000,
                'observatii' => 'Vizionare marti, 26 mai 2026, ora 17:40.',
            ]),
            array_merge($defaults, [
                'adresa' => "Chaussee d'Etterbeek 57",
                'localitate' => 'Bruxelles',
                'status' => 'astept_raspuns',
                'pret' => 520000,
                'observatii' => 'Astept raspuns pentru vizionare.',
            ]),
            array_merge($defaults, [
                'adresa' => 'Avenue des Nerviens 79 boite 6',
                'localitate' => '1040 Etterbeek',
                'status' => 'programat',
                'vizionare_at' => '2026-05-23 10:00:00',
                'pret' => 580000,
                'etaj' => 2,
                'observatii' => 'Vizionare pe 23/05/2026 la 10:00. Etaj/position 2.',
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('apps_apartamente');
    }
};
