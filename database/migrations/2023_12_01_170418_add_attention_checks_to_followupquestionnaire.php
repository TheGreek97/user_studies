<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('followupquestionnaire', function (Blueprint $table) {
            $table->tinyInteger('understood_warning_reverse')->nullable();
            $table->integer('nasa_mental_demand_reverse')->nullable();
            $table->boolean("cyber_control")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('followupquestionnaire', function (Blueprint $table) {
            $table->dropColumn('understood_warning_reverse');
            $table->dropColumn('nasa_mental_demand_reverse');
            $table->dropColumn('cyber_control');
        });
    }
};
