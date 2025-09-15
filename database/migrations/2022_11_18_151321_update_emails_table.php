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
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('warning_type');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->enum('warning_type', ['popup_email', 'popup_link', 'tooltip', 'browser_native', 'base_passive', 'no_warning'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('warning_type');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->enum('warning_type', ['popup_email', 'popup_link', 'tooltip', 'browser_native'])->nullable();
        });
    }
};
