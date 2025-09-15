<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stp_ii_bs', function (Blueprint $table) {
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
            $table->tinyInteger('q16');
            $table->tinyInteger('q17');
            $table->tinyInteger('q18');
            $table->tinyInteger('q19');
            $table->tinyInteger('q20');
            $table->tinyInteger('q21');
            $table->tinyInteger('q22');
            $table->tinyInteger('q23');
            $table->tinyInteger('q24');
            $table->tinyInteger('q25');
            $table->tinyInteger('q26');
            $table->tinyInteger('q27');
            $table->tinyInteger('q28');
            $table->tinyInteger('q29');
            $table->tinyInteger('q30');
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
        Schema::dropIfExists('stp_ii_bs');
    }
};
