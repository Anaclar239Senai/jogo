<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('player_name');
            $table->string('player_element');
            $table->integer('player_health')->default(100);
            $table->integer('player_level')->default(1);
            $table->integer('current_phase')->default(1);
            $table->integer('score')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('games');
    }
}