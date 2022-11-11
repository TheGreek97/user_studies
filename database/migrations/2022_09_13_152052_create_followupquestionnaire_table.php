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
        Schema::create('followupquestionnaire', function (Blueprint $table) {
            $table->id();
            //SECTION 1
            $table->string('parts_email_suspicious')->nullable();
            $table->integer('know_email_1')->nullable();
            $table->integer('know_email_2')->nullable();
            //SECTION 2
            $table->boolean('have_read_warning')->default(false);
            $table->integer('how_warning_useful_identifying_link');
            $table->integer('how_annoying_warning_was');
            $table->integer('how_warning_perception_link');
            $table->integer('how_evident_was_warning');
            $table->longText('explanation_feedback')->nullable();

            //SECTION 3 - NASA-TLX
            $table->integer('nasa_mental_demand')->nullable();
            $table->integer('nasa_physical_demand')->nullable();
            $table->integer('nasa_temporal_demand')->nullable();
            $table->integer('nasa_performance')->nullable();
            $table->integer('nasa_effort')->nullable();
            $table->integer('nasa_frustration_level')->nullable();
            //SECTION 4 - CYBERSEC KNOWLEDGE
            $table->string('cyber_1');
            $table->string('cyber_2');
            $table->string('cyber_3');
            $table->string('cyber_4');
            $table->string('cyber_5');
            $table->string('cyber_6');
            $table->string('cyber_7');
            $table->string('cyber_8');
            $table->string('cyber_9');
            $table->string('cyber_10');
            //OTHERS
            $table->unsignedBigInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('followupquestionnaire');
    }
};
