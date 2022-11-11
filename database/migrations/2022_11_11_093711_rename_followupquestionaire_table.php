<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $from = "followupquestionnaire";
    private $to = "skillsquestionnaire";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename($this->from, $this->to);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename($this->to, $this->from);
    }
};
