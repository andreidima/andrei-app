<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agencies')) {
            Schema::create('agencies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('website')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agency_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['agency_id', 'name']);
            });
        }

        if (! Schema::hasColumn('apartamente', 'agency_id')) {
            Schema::table('apartamente', function (Blueprint $table) {
                $table->foreignId('agency_id')->nullable()->after('contact')->constrained('agencies')->nullOnDelete();
                $table->foreignId('agent_id')->nullable()->after('agency_id')->constrained('agents')->nullOnDelete();
                $table->string('sursa_anunt')->nullable()->after('link_anunt');
                $table->string('referinta_anunt')->nullable()->after('sursa_anunt');
                $table->string('decizie', 50)->nullable()->after('status');
                $table->text('motiv_respingere')->nullable()->after('decizie');
                $table->unsignedTinyInteger('prioritate')->nullable()->after('motiv_respingere');
                $table->unsignedInteger('pret_maxim_oferta')->nullable()->after('pret');
                $table->unsignedTinyInteger('bai')->nullable()->after('camere');
                $table->unsignedTinyInteger('toalete')->nullable()->after('bai');
                $table->unsignedSmallInteger('an_constructie')->nullable()->after('etaj');
                $table->unsignedTinyInteger('etaje_cladire')->nullable()->after('an_constructie');
                $table->string('stare_cladire')->nullable()->after('etaje_cladire');
                $table->string('stare_apartament')->nullable()->after('stare_cladire');
                $table->string('tip_incalzire')->nullable()->after('peb');
                $table->unsignedSmallInteger('peb_consum')->nullable()->after('tip_incalzire');
                $table->boolean('electricitate_conforma')->nullable()->after('peb_consum');
                $table->boolean('are_pivnita')->nullable()->after('are_parcare');
                $table->string('orientare_terasa')->nullable()->after('orientare_lumina');
                $table->date('disponibil_din')->nullable()->after('zona');
                $table->unsignedInteger('venit_cadastral')->nullable()->after('costuri_extra_estimate');
                $table->string('motivatie_achizitie')->nullable()->after('venit_cadastral');
            });
        }

        if (Schema::hasTable('apartment_interactions') && DB::table('apartment_interactions')->count() === 0) {
            Schema::dropIfExists('apartment_interactions');
        }

        if (! Schema::hasTable('apartment_interactions')) {
            Schema::create('apartment_interactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('apartament_id');
                $table->foreignId('agency_id')->nullable()->constrained('agencies')->nullOnDelete();
                $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
                $table->string('type', 50);
                $table->dateTime('interacted_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('apartament_id')->references('id')->on('apartamente')->cascadeOnDelete();
            });
        }

        $this->backfillKnownListings();
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_interactions');

        Schema::table('apartamente', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropForeign(['agent_id']);
            $table->dropColumn([
                'agency_id',
                'agent_id',
                'sursa_anunt',
                'referinta_anunt',
                'decizie',
                'motiv_respingere',
                'prioritate',
                'pret_maxim_oferta',
                'bai',
                'toalete',
                'an_constructie',
                'etaje_cladire',
                'stare_cladire',
                'stare_apartament',
                'tip_incalzire',
                'peb_consum',
                'electricitate_conforma',
                'are_pivnita',
                'orientare_terasa',
                'disponibil_din',
                'venit_cadastral',
                'motivatie_achizitie',
            ]);
        });

        Schema::dropIfExists('agents');
        Schema::dropIfExists('agencies');
    }

    private function backfillKnownListings(): void
    {
        DB::table('agencies')->updateOrInsert(
            ['name' => 'Latour & Petit'],
            [
                'website' => 'https://latouretpetit.be',
                'phone' => '+32 2 777 19 19',
                'email' => 'info@latouretpetit.be',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $latourAgencyId = DB::table('agencies')->where('name', 'Latour & Petit')->value('id');

        DB::table('agents')->updateOrInsert(
            ['agency_id' => $latourAgencyId, 'name' => 'Laurence Agneessens'],
            [
                'email' => 'laurence@latouretpetit.be',
                'phone' => '+32 2 777 19 19',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $latourAgentId = DB::table('agents')
            ->where('agency_id', $latourAgencyId)
            ->where('name', 'Laurence Agneessens')
            ->value('id');

        DB::table('agencies')->updateOrInsert(
            ['name' => 'IVA Immobiliere'],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $ivaAgencyId = DB::table('agencies')->where('name', 'IVA Immobiliere')->value('id');

        DB::table('agents')->updateOrInsert(
            ['agency_id' => $ivaAgencyId, 'name' => 'Germain'],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $ivaAgentId = DB::table('agents')
            ->where('agency_id', $ivaAgencyId)
            ->where('name', 'Germain')
            ->value('id');

        DB::table('apartamente')
            ->where('adresa', 'Rue Froissart 135')
            ->update([
                'localitate' => '1040 Bruxelles',
                'status' => 'programat',
                'link_anunt' => 'https://www.immoweb.be/fr/annonce/appartement/a-vendre/bruxelles/1040/21575584',
                'sursa_anunt' => 'Immoweb',
                'referinta_anunt' => '21575584',
                'agency_id' => $ivaAgencyId,
                'agent_id' => $ivaAgentId,
                'agentie' => 'IVA Immobiliere',
                'contact' => 'Germain',
                'pret' => 425000,
                'cheltuieli_lunare' => 220,
                'suprafata_mp' => 110,
                'camere' => 2,
                'etaj' => 6,
                'etaje_cladire' => 7,
                'bai' => 2,
                'toalete' => 2,
                'peb' => 'D',
                'peb_consum' => 206,
                'tip_incalzire' => 'Electric',
                'electricitate_conforma' => true,
                'are_lift' => true,
                'are_balcon' => true,
                'are_pivnita' => true,
                'stare_cladire' => 'Buna',
                'stare_apartament' => 'Excelenta',
                'zona' => 'Schuman / cartierul european',
                'observatii' => DB::raw("CONCAT(COALESCE(observatii, ''), IF(COALESCE(observatii, '') = '', '', '\n'), 'Immoweb 21575584: 2 camere, 110 mp, etaj 6/7, balcon, pivnita, lift, PEB D, electricitate conforma. Charges comune aproximativ 220 EUR/luna.')"),
                'updated_at' => now(),
            ]);

        DB::table('apartamente')
            ->where("adresa", "Chaussee d'Etterbeek 57")
            ->update([
                'localitate' => '1040 Bruxelles',
                'status' => 'programat',
                'link_anunt' => 'https://latouretpetit.be/nos-biens/vente/1040-bruxelles-appartement-2-chambres/7663888',
                'sursa_anunt' => 'Latour & Petit',
                'referinta_anunt' => '7663888',
                'agency_id' => $latourAgencyId,
                'agent_id' => $latourAgentId,
                'agentie' => 'Latour & Petit',
                'contact' => 'Laurence Agneessens / laurence@latouretpetit.be / +32 2 777 19 19',
                'pret' => 520000,
                'suprafata_mp' => 95,
                'camere' => 2,
                'etaj' => 3,
                'bai' => 2,
                'an_constructie' => 2013,
                'peb' => 'C',
                'are_balcon' => true,
                'are_parcare' => true,
                'are_pivnita' => true,
                'orientare_terasa' => 'Sud-Est',
                'disponibil_din' => '2026-07-01',
                'stare_apartament' => 'Foarte buna',
                'zona' => 'Etterbeek',
                'observatii' => DB::raw("CONCAT(COALESCE(observatii, ''), IF(COALESCE(observatii, '') = '', '', '\n'), 'Latour & Petit 7663888: 2 camere, 95 mp, etaj 3, terasa Sud-Est, pivnita si parcare incluse, cladire 2013, PEB C, disponibil din iulie 2026.')"),
                'updated_at' => now(),
            ]);

        DB::table('apartamente')
            ->whereIn('status', ['programat', 'vazut', 'astept_raspuns'])
            ->orderBy('id')
            ->get()
            ->each(function ($apartament) {
                DB::table('apartment_interactions')->insert([
                    'apartament_id' => $apartament->id,
                    'agency_id' => $apartament->agency_id,
                    'agent_id' => $apartament->agent_id,
                    'type' => $apartament->status === 'vazut' ? 'visited' : ($apartament->status === 'astept_raspuns' ? 'visit_requested' : 'visit_scheduled'),
                    'interacted_at' => $apartament->vizionare_at,
                    'notes' => $apartament->observatii,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }
};
