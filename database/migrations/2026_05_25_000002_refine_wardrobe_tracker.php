<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wardrobe_people', function (Blueprint $table) {
            $table->string('contact_type')->default('person')->after('name');
        });

        DB::table('wardrobe_people')
            ->where('name', 'like', '%&%')
            ->update(['contact_type' => 'couple']);

        DB::table('wardrobe_clothing_items')
            ->whereIn('category', ['Shirt', 'Shirts', 'shirts'])
            ->update(['category' => 'shirt']);

        DB::table('wardrobe_clothing_items')
            ->whereIn('category', ['Short', 'Shorts'])
            ->update(['category' => 'shorts']);
    }

    public function down(): void
    {
        Schema::table('wardrobe_people', function (Blueprint $table) {
            $table->dropColumn('contact_type');
        });
    }
};
