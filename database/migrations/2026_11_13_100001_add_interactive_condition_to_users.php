<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the thesis condition "interactive" (personalized training with structured data for
 * the interactive modules and the Module 4 tutor) to users.training_personalization.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users
            MODIFY training_personalization
            ENUM('no', 'yes', 'primed', 'few_shot', 'table', 'interactive')
            NOT NULL DEFAULT 'no'");
    }

    public function down(): void
    {
        // Refuse instead of silently relabelling participants: mapping 'interactive' to 'yes' would mix the thesis
        // condition into a paper condition and corrupt the analysis. Export/remove those users first.
        $interactive = DB::table('users')->where('training_personalization', 'interactive')->count();
        if ($interactive > 0) {
            throw new RuntimeException(
                "Refusing to roll back: {$interactive} user(s) have training_personalization = 'interactive'."
            );
        }
        DB::statement("ALTER TABLE users
            MODIFY training_personalization
            ENUM('no', 'yes', 'primed', 'few_shot', 'table')
            NOT NULL DEFAULT 'no'");
    }
};
