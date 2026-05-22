<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('admin')->after('email');
        });

        DB::table('users')->update(['role' => 'admin']);

        User::updateOrCreate(
            ['email' => 'florinacercel@gmail.com'],
            [
                'name' => 'Florina Dima',
                'password' => Hash::make('222aaa888bbb333ccc'),
                'role' => 'apartments',
            ]
        );
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
