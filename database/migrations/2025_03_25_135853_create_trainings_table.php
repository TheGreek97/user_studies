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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText("introduction")->nullable();
            $table->longText("scenario")->nullable();
            $table->longText("defense_strategies")->nullable();
            $table->longText("exercises")->nullable();
            $table->longText("conclusions")->nullable();

            $table->boolean("generated")->default(false);
            $table->foreignId("user_id")->constrained("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trainings');
    }
};
