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
            $table->string('type')->nullable();
            $table->timestamps();
            $table->index('market_hash_name');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('knives');
    }
}
