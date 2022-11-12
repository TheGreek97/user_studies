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
        Schema::create('questionnaire_skills', function (Blueprint $table) {
            $table->id();

            //SECTION 4 - CYBERSEC KNOWLEDGE
            $table->string('level_informatics');
            $table->string('level_cybersecurity');
            $table->string('level_iot');
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
        Schema::dropIfExists('questionnaire_skills');
    }
};
