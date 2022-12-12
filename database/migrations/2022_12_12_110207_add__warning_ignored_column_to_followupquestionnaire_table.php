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
            $table->string('warning_ignored_motivation', 255)->nullable();
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
            $table->dropColumn('warning_ignored_motivation');
        });
    }
};
