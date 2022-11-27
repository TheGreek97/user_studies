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
        Schema::create('questionnaire_advanced_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');

            $table->text('answer_1')->nullable();
            $table->text('answer_1_rationale')->nullable();
            $table->text('answer_1_alt')->nullable();
            $table->text('answer_2')->nullable()->nullable();
            $table->text('answer_2_rationale')->nullable();
            $table->text('answer_2_alt')->nullable();
            $table->text('answer_3')->nullable();
            $table->text('answer_3_rationale')->nullable();
            $table->text('answer_3_alt')->nullable();
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
        Schema::dropIfExists('questionnaire_advanced_user');
    }
};
