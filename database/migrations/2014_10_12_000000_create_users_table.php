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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            //$table->integer('age')->nullable();
            //$table->integer('num_hours_day_internet')->nullable();
            //$table->enum('gender', ['Male', 'Female', 'Others', 'Prefer not to say'])->nullable();
            $table->enum('expertise', ['basic', 'medium', 'expert'])->nullable()->default('basic');
            //$table->enum('warning_type', ['popup_email', 'popup_link', 'tooltip', 'browser_native']);
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
        Schema::dropIfExists('users');
    }
};
