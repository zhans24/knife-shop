<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('knives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('rarity');
            $table->decimal('float_value', 5, 2);
            $table->enum('wear_level', ['Factory New', 'Minimal Wear', 'Field-Tested', 'Well-Worn', 'Battle-Scarred']);
            $table->decimal('price', 8, 2);
            $table->text('description')->nullable();
            $table->string('color');
            $table->string('image_url');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knives');
    }
};
