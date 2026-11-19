<?php

namespace Tests\Unit\Training;

use App\Services\Training\Conclusions;
use PHPUnit\Framework\TestCase;

/** Split of the generated conclusions into recap and the "thanks" part shown at the end of Module 5. */
class ConclusionsTest extends TestCase
{
    public function test_splits_recap_and_thanks_keeping_utf8(): void
    {
        $html = '<div class="mb-10"><p class="mb-4">Phishing awareness matters – don’t rush.</p><ul><li>Check the sender</li></ul>'
            .'<div data-role="thanks"><p>Thank you, Zoë!</p></div></div>';
        $parts = Conclusions::split($html);

        $this->assertSame('<div class="mb-10"><p class="mb-4">Phishing awareness matters – don’t rush.</p><ul><li>Check the sender</li></ul></div>', $parts['recap']);
        $this->assertSame('<div data-role="thanks"><p>Thank you, Zoë!</p></div>', $parts['thanks']);
    }

    public function test_without_a_thanks_part_the_html_is_unchanged(): void
    {
        $html = '<div><p>Recap only, data-role="other" nowhere.</p></div>';
        $this->assertSame(['recap' => $html, 'thanks' => null], Conclusions::split($html));
        $this->assertSame(['recap' => '', 'thanks' => null], Conclusions::split(''));
    }
}
