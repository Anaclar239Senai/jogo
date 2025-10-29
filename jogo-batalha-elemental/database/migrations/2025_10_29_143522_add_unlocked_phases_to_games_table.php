<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnlockedPhasesToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->integer('unlocked_phases')->default(1)->after('current_phase');
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('unlocked_phases');
        });
    }
}