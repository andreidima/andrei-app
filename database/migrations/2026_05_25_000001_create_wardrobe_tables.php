<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wardrobe_people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wardrobe_clothing_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('color')->nullable();
            $table->string('brand')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wardrobe_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->dateTime('met_at');
            $table->string('location')->nullable();
            $table->text('clothes_description')->nullable();
            $table->string('outfit_photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wardrobe_meeting_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('wardrobe_meetings')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('wardrobe_people')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meeting_id', 'person_id']);
        });

        Schema::create('wardrobe_clothing_item_meeting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('wardrobe_meetings')->cascadeOnDelete();
            $table->foreignId('clothing_item_id')->constrained('wardrobe_clothing_items')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meeting_id', 'clothing_item_id'], 'wardrobe_item_meeting_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wardrobe_clothing_item_meeting');
        Schema::dropIfExists('wardrobe_meeting_person');
        Schema::dropIfExists('wardrobe_meetings');
        Schema::dropIfExists('wardrobe_clothing_items');
        Schema::dropIfExists('wardrobe_people');
    }
};
