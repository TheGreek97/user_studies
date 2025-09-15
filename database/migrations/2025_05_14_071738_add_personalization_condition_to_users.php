<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
         DB::statement("ALTER TABLE users
            MODIFY training_personalization
            ENUM('no', 'yes', 'primed', 'few_shot', 'table')
            NOT NULL DEFAULT 'no'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Replace any 'yes_no_trait_desc' values to a fallback value 'yes'
        DB::table('users')
            ->whereIn('training_personalization', ['few_shot', 'table'])
            ->update(['training_personalization' => 'yes']);
        // Change the column back
        DB::statement("ALTER TABLE users
            MODIFY training_personalization
            ENUM('no', 'yes', 'primed')
            NOT NULL DEFAULT 'no'");
    }
};
