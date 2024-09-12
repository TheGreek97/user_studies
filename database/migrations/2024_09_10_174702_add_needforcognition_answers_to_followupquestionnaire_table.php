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
            for ($i = 1; $i <= 18; $i++) {
                $table->integer('n4c_'.$i)->nullable();
            }
            $table->integer('n4c_attention')->nullable();
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
            for ($i = 1; $i <= 18; $i++) {
                $table->dropColumn('n4c_'.$i);
            }
            $table->dropColumn('n4c_attention');
        });
    }
};
