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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp("demographics_completed")->nullable();
            $table->timestamp("bfi_completed")->nullable();
            $table->timestamp("stp_completed")->nullable();
            $table->timestamp("teique_completed")->nullable();
            $table->timestamp("training_reaction_completed")->nullable();
            $table->timestamp("questionnaires_completed")->nullable();
            $table->timestamp("pre_training_completed")->nullable();
            $table->timestamp("training_completed")->nullable();
            $table->timestamp("post_training_completed")->nullable();
        });
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('demographics_completed');
            $table->dropColumn('bfi_completed');
            $table->dropColumn('teique_completed');
            $table->dropColumn('stp_completed');
            $table->dropColumn('training_reaction_completed');
            $table->dropColumn("questionnaires_completed");
            $table->dropColumn("pre_training_completed");
            $table->dropColumn("training_completed");
            $table->dropColumn("post_training_completed");
        });
        Schema::table('trainings', function (Blueprint $table) {
            $table->timestamp('completed_at');
        });
    }
};
