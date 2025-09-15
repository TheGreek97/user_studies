<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bfi_2_xss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('q1');
            $table->tinyInteger('q2');
            $table->tinyInteger('q3');
            $table->tinyInteger('q4');
            $table->tinyInteger('q5');
            $table->tinyInteger('q6');
            $table->tinyInteger('q7');
            $table->tinyInteger('q8');
            $table->tinyInteger('q9');
            $table->tinyInteger('q10');
            $table->tinyInteger('q11');
            $table->tinyInteger('q12');
            $table->tinyInteger('q13');
            $table->tinyInteger('q14');
            $table->tinyInteger('q15');
            $table->boolean('trivial_question');
            $table->tinyInteger('fastClickCount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bfi_2_xss');
    }
};
