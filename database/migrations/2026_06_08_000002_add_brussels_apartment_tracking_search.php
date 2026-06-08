<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('apartment_searches')) {
            return;
        }

        DB::table('apartment_searches')->updateOrInsert(
            [
                'source' => 'immoweb',
                'url' => 'https://www.immoweb.be/fr/recherche/appartement/a-vendre?buildingConditions=GOOD,AS_NEW,JUST_RENOVATED&countries=BE&epcScores=A++,A+,B,D,C&maxPrice=500000&minBedroomCount=2&postalCodes=1000,1030,1040,1050,1150,1200,1210&isNewlyBuilt=false&page=1&orderBy=relevance',
            ],
            [
                'name' => 'Bruxelles - 2 chambres - max 500k - bon etat',
                'neighborhood' => '1000, 1030, 1040, 1050, 1150, 1200, 1210',
                'is_active' => true,
                'notes' => 'Appartement a vendre, 2+ chambres, max 500k, PEB A++/A+/B/C/D, bon etat/as new/renove, hors neuf.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('apartment_searches')) {
            return;
        }

        DB::table('apartment_searches')
            ->where('source', 'immoweb')
            ->where('url', 'https://www.immoweb.be/fr/recherche/appartement/a-vendre?buildingConditions=GOOD,AS_NEW,JUST_RENOVATED&countries=BE&epcScores=A++,A+,B,D,C&maxPrice=500000&minBedroomCount=2&postalCodes=1000,1030,1040,1050,1150,1200,1210&isNewlyBuilt=false&page=1&orderBy=relevance')
            ->delete();
    }
};
