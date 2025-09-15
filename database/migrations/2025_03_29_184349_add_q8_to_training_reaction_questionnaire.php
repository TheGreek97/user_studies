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
        Schema::table('training_reaction', function (Blueprint $table) {
            $table->string('q7')->nullable()->change();
            $table->string('q8')->nullable()->after('q7');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('training_reaction', function (Blueprint $table) {
            $table->string('q7')->change();
            $table->dropColumn('q8');
        });
    }
};
