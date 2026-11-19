<?php

namespace Tests\Feature\Llm;

use App\Http\Controllers\TrainingController;
use App\Jobs\ProcessTraining;
use App\Models\Training;
use App\Services\Training\FirstName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InteractiveTrainingRenderTest extends TestCase
{
    use RefreshDatabase;

    private const SECTIONS = [
        'introduction' => '<div><p>Hello {{first_name}}, welcome.</p></div>',
        'scenario' => '<div><p>Imagine, {{first_name}}.</p></div>',
        'defense_strategies' => '<div><p>Strategies &amp; tips.</p></div>',
        'exercises' => '<div><p>Exercises for {{first_name}}.</p></div>',
        'conclusions' => '<div><p>Recap.</p><div data-role="thanks">Thank you, {{first_name}}.</div></div>',
    ];

    private function renderTrainingFor(string $personalization, string $name): string
    {
        $user = ProcessTrainingTest::participant($personalization);
        $user->update(['name' => $name]);
        Training::create(['user_id' => $user->id, 'generated' => true] + self::SECTIONS);

        $this->actingAs($user->fresh());
        return app(TrainingController::class)->showTraining()->render();
    }

    public function test_first_name_is_replaced_and_escaped_for_interactive_trainings(): void
    {
        $html = $this->renderTrainingFor('interactive', '<b>Al&ice</b>');

        $this->assertStringNotContainsString(FirstName::PLACEHOLDER, $html);
        $this->assertStringNotContainsString('<b>Al&ice</b>', $html);
        $this->assertStringContainsString('Hello &lt;b&gt;Al&amp;ice&lt;/b&gt;, welcome.', $html);
        // The thanks part of the conclusions is shown only at the end of Module 5 (escaping: PersonalRulesTest).
        $this->assertStringNotContainsString('Thank you,', $html);
        $this->assertStringContainsString('Recap.', $html);
        // Existing entities are still decoded as for the paper's conditions.
        $this->assertStringContainsString('Strategies & tips.', $html);
    }

    public function test_paper_conditions_render_as_before(): void
    {
        $html = $this->renderTrainingFor('yes', 'Alice');

        // No replacement outside the interactive condition (their prompts never ask for the placeholder).
        $this->assertStringContainsString('Hello {{first_name}}, welcome.', $html);
    }

    public function test_render_section_equals_html_entity_decode_for_paper_conditions(): void
    {
        $user = ProcessTrainingTest::participant('table');
        $training = Training::create(['user_id' => $user->id, 'generated' => true] + self::SECTIONS);

        foreach (array_keys(self::SECTIONS) as $section) {
            $this->assertSame(html_entity_decode($training->$section), $training->renderSection($section));
        }
    }

    public function test_fill_text_does_not_escape(): void
    {
        $this->assertSame('To: A&B', FirstName::fillText('To: {{first_name}}', 'A&B'));
        $this->assertSame('To: A&amp;B', FirstName::fillHtml('To: {{first_name}}', 'A&B'));
    }

    public function test_create_training_dispatches_generation_for_interactive(): void
    {
        Queue::fake();
        $user = ProcessTrainingTest::participant('interactive');
        $this->actingAs($user);

        $training = TrainingController::createTraining();

        $this->assertFalse($training->generated);
        Queue::assertPushed(ProcessTraining::class, 1);
    }
}
