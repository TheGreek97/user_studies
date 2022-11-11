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
        Schema::create('useremailquestionnaire', function (Blueprint $table) {
            $table->id();
            $table->string("title_email");
            $table->string("sender_email");
            $table->string("how_many_hyperlinks");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("email_id");
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('email_id')->references('id')->on('emails');
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
        Schema::dropIfExists('useremailquestionnaire');
    }
};
