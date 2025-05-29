<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKnivesTable extends Migration
{
    public function up()
    {
        Schema::create('knives', function (Blueprint $table) {
            $table->id();
            $table->string('market_hash_name')->unique();
            $table->string('type');
            $table->string('steam_name')->nullable();
            $table->string('wear_level')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('steam_data_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('knives');
    }
}
