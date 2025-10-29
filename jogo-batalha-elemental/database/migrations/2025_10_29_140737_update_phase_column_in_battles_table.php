<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePhaseColumnInBattlesTable extends Migration
{
    public function up()
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->string('phase')->change(); // Muda de integer para string
        });
    }

    public function down()
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->integer('phase')->change(); // Reverte para integer
        });
    }
}