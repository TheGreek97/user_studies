<?php

namespace Tests\Feature\Migrations;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/** Rollback of 2026_11_13_100001 must never relabel "interactive" participants as a paper condition. */
class InteractiveConditionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rollback_is_refused_while_interactive_users_exist(): void
    {
        $user = User::factory()->create(['training_personalization' => 'interactive']);
        $migration = require database_path('migrations/2026_11_13_100001_add_interactive_condition_to_users.php');

        try {
            $migration->down();
            $this->fail('down() should refuse the rollback');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('1 user(s)', $e->getMessage());
        }
        // Nothing was changed: the check runs before any statement.
        $this->assertSame('interactive', $user->fresh()->training_personalization);
    }
}
