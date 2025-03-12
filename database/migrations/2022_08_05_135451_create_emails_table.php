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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('from_name');
            $table->string('from_email');
            $table->string('subject');
            $table->string('preview_text');
            $table->longText('content');
            $table->dateTime('date');
            $table->enum('type',['inbox', 'sent', 'draft', 'trash']);
            $table->enum('warning_type', ['popup_email', 'popup_link', 'tooltip', 'browser_native'])->nullable();
            $table->longText('warning_explanation_1')->nullable();
            $table->longText('warning_explanation_2')->nullable();
            // New fields:
            $table->enum('difficulty_level', ['low', 'medium', 'high']);
            $table->boolean('phishing')->default(0);  // 0 = false, 1 = true
            $table->boolean('counterpart')->default(0);  // 0 = false, 1 = true
            //$table->unsignedBigInteger('user_id');
            //$table->timestamps();
            //$table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
};
