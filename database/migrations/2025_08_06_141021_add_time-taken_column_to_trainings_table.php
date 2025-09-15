<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns safely (idempotent)
        Schema::table('trainings', function (Blueprint $table) {
            if (!Schema::hasColumn('trainings', 'time_taken')) {
                $table->unsignedInteger('time_taken')
                    ->default(0)
                    ->after('modules');
            }

            // Only add completed_at if it doesn't already exist
            if (!Schema::hasColumn('trainings', 'completed_at')) {
                $table->timestamp('completed_at')
                    ->nullable()
                    ->after('time_taken');
            }
        });
    }

    public function down(): void
    {
        // Drop only if present to avoid rollback errors
        if (Schema::hasColumn('trainings', 'time_taken')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('time_taken');
            });
        }

        if (Schema::hasColumn('trainings', 'completed_at')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};
