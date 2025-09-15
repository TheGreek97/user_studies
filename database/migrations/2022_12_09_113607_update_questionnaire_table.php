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
            $table->dropColumn('parts_email_suspicious');
            $table->dropColumn('know_email_1');
            $table->dropColumn('know_email_2');
            $table->dropColumn('have_read_warning');
            $table->dropColumn('how_warning_useful_identifying_link');
            $table->dropColumn('how_annoying_warning_was');
            $table->dropColumn('how_warning_perception_link');
            $table->dropColumn('how_evident_was_warning');
            $table->dropColumn('explanation_feedback');
        });

        Schema::table('followupquestionnaire', function (Blueprint $table) {
            $table->tinyInteger('read_warning')->nullable();
            $table->string('reaction', 255)->nullable();
            $table->tinyInteger('understood_warning')->nullable();
            $table->tinyInteger('familiar_warning')->nullable();
            $table->tinyInteger('interested_warning')->nullable();
            $table->string('confusing_words', 255)->nullable();
            $table->tinyInteger('felt_risk')->nullable();
            $table->enum("actions_warning", ["continue", "be_careful", "not_continue", "none"]);
            $table->string('meaning_warning', 255)->nullable();
            $table->tinyInteger('trust_warning')->nullable();
            $table->string('first_word', 255)->nullable();
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
            $table->dropColumn('read_warning');
            $table->dropColumn('reaction');
            $table->dropColumn('understood_warning');
            $table->dropColumn('familiar_warning');
            $table->dropColumn('interested_warning');
            $table->dropColumn('confusing_words');
            $table->dropColumn('felt_risk');
            $table->dropColumn("actions_warning");
            $table->dropColumn('meaning_warning');
            $table->dropColumn('trust_warning');
            $table->dropColumn('first_word');
        });
        Schema::table('followupquestionnaire', function (Blueprint $table) {
            $table->string('parts_email_suspicious');
            $table->integer('know_email_1');
            $table->integer('know_email_2');
            $table->boolean('have_read_warning')->default(false);
            $table->integer('how_warning_useful_identifying_link');
            $table->integer('how_annoying_warning_was');
            $table->integer('how_warning_perception_link');
            $table->integer('how_evident_was_warning');
            $table->longText('explanation_feedback')->nullable();
        });
    }
};
