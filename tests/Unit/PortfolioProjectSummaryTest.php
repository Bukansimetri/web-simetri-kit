<?php

namespace Tests\Unit;

use App\Models\PortfolioProject;
use PHPUnit\Framework\TestCase;

class PortfolioProjectSummaryTest extends TestCase
{
    public function test_long_description_is_truncated_and_html_removed(): void
    {
        $project = new PortfolioProject(['description' => '<p>'.str_repeat('Kata ', 80).'</p>']);

        $summary = $project->summary();

        $this->assertLessThanOrEqual(140, mb_strlen($summary));
        $this->assertStringEndsWith('...', $summary);
        $this->assertStringNotContainsString('<p>', $summary);
    }

    public function test_entities_are_decoded_and_whitespace_squished(): void
    {
        $project = new PortfolioProject(['description' => "<p>Hemat&nbsp;&amp;\n  efisien</p>"]);

        $this->assertSame('Hemat & efisien', str_replace("\u{a0}", ' ', $project->summary()));
    }

    public function test_empty_description_gives_empty_summary(): void
    {
        $this->assertSame('', (new PortfolioProject(['description' => null]))->summary());
    }
}
