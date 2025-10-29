<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattlesTable extends Migration
{
    public function up()
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('phase');
            $table->string('enemy_element');
            $table->integer('enemy_health')->default(100);
            $table->integer('player_health')->default(100);
            $table->string('status')->default('active');
            $table->json('battle_log')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('battles');
    }
}