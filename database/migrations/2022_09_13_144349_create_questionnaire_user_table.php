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
        Schema::create('questionnaire_user', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');

            $table->text('answer_1');
            $table->text('answer_1_rationale');
            $table->text('answer_1_alt')->nullable();
            $table->text('answer_2');
            $table->text('answer_2_rationale');
            $table->text('answer_2_alt')->nullable();
            $table->text('answer_3');
            $table->text('answer_3_rationale');
            $table->text('answer_3_alt')->nullable();
            $table->text('answer_4');
            $table->text('answer_4_rationale');
            $table->text('answer_4_alt')->nullable();
            $table->text('answer_5');
            $table->text('answer_5_rationale');
            $table->text('answer_5_alt')->nullable();
            $table->text('answer_6');
            $table->text('answer_6_rationale');
            $table->text('answer_6_alt')->nullable();

            $table->text('other_events')->nullable();
            $table->text('other_actions')->nullable();
            $table->text('preferred_level')->nullable();
            $table->text('alternatives')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionnaire_user');
    }
};
