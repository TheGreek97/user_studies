<?php

namespace Tests\Unit\Tutor;

use App\Services\Tutor\Text;

/** Port of test/text.test.ts. */
class TextTest extends TutorTestCase
{
    public function test_counts_sentences_without_splitting_abbreviations_or_domains(): void
    {
        $this->assertSame(2, Text::sentenceCount('You noticed the deadline. What else would you check?'));
        $this->assertSame(2, Text::sentenceCount('Look at the sender, e.g. the part after the @ in airbnb.com. What do you notice?'));
        $this->assertSame(4, Text::sentenceCount('One. Two! Three? Four.'));
        $this->assertSame(1, Text::sentenceCount('The link says ..., but the address ends with ___'));
    }

    public function test_counts_question_marks_and_words(): void
    {
        $this->assertSame(2, Text::questionCount('Why? Where?'));
        $this->assertSame(2, Text::wordCount('looks fake'));
        $this->assertSame(4, Text::wordCount('  the sender is odd '));
    }

    public function test_detects_minimal_replies_but_not_i_dont_know(): void
    {
        foreach (['ok', 'Yes.', 'no', 'sure!', 'maybe'] as $t) {
            $this->assertTrue(Text::isMinimalReply($t), $t);
        }
        $this->assertFalse(Text::isMinimalReply("I don't know"));
        $this->assertFalse(Text::isMinimalReply('The sender domain is different'));
    }

    public function test_detects_dont_know_and_answer_requests(): void
    {
        $this->assertTrue(Text::isDontKnow("I don't know."));
        $this->assertTrue(Text::isDontKnow('no idea'));
        $this->assertFalse(Text::isDontKnow('I know it'));
        $this->assertTrue(Text::isAnswerRequest('Just tell me, is it phishing or not?'));
        $this->assertTrue(Text::isAnswerRequest('Can I skip this one?'));
        $this->assertFalse(Text::isAnswerRequest('The sender is odd'));
    }
}
