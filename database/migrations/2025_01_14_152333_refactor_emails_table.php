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
        Schema::table('emails', function (Blueprint $table) {
            $table->string("phishing_url")->nullable();
            $table->string("phishing_feature")->nullable();
            // Substitute the two fields containing the explanation with just one
            $table->string("warning_explanation")->nullable();
            $table->dropColumn("warning_explanation_1");
            $table->dropColumn("warning_explanation_2");
            // Make date nullable
            $table->dateTime('date')->default(now())->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn("phishing_url");
            $table->dropColumn("phishing_feature");
            $table->dropColumn("warning_explanation");
            $table->longText('warning_explanation_1')->nullable();
            $table->longText('warning_explanation_2')->nullable();
            $table->dateTime('date')->change();
        });
    }
};
