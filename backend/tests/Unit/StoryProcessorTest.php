<?php

namespace Tests\Unit;

use App\Services\StoryProcessor;
use PHPUnit\Framework\TestCase;

class StoryProcessorTest extends TestCase {
    public function test_stories_are_classified_by_title_word_count(): void {
        $stories = [
            [
                'id' => 50644817,
                'title' => '1Password Security Incident',
                'points' => 880,
                'comments' => 378,
            ],
            [
                'id' => 50646108,
                'title' => 'OMG OMG OMG, I can’t imagine: The magnitude of what’s now happening',
                'points' => 659,
                'comments' => 480,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);

        $this->assertCount(1, $result['short_titles']);
        $this->assertCount(1, $result['long_titles']);

        $this->assertSame(50644817, $result['short_titles'][0]['id']);
        $this->assertSame(50646108, $result['long_titles'][0]['id']);
    }

    public function test_long_titles_are_sorted_by_comments_descending(): void {
        $stories = [
            [
                'id' => 50642086,
                'title' => 'Google releases Cogentic papers on integer factorization, discrete logs and more',
                'points' => 411,
                'comments' => 315,
            ],
            [
                'id' => 50637220,
                'title' => 'The matrix multiplication exponent is nine fourths [pdf]',
                'points' => 998,
                'comments' => 553,
            ],
            [
                'id' => 50646108,
                'title' => 'OMG OMG OMG, I can’t imagine: The magnitude of what’s now happening',
                'points' => 659,
                'comments' => 480,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);

        $this->assertSame(
            [50637220, 50646108, 50642086],
            array_column($result['long_titles'], 'id'),
        );
    }

    public function test_short_titles_are_sorted_by_points_descending(): void {
        $stories = [
            [
                'id' => 50645074,
                'title' => 'Britain’s ghost stations',
                'points' => 69,
                'comments' => 16,
            ],
            [
                'id' => 50644817,
                'title' => '1Password Security Incident',
                'points' => 880,
                'comments' => 378,
            ],
            [
                'id' => 50589784,
                'title' => 'SSH is a GUI framework',
                'points' => 91,
                'comments' => 48,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);

        $this->assertSame(
            [50644817, 50589784, 50645074],
            array_column($result['short_titles'], 'id'),
        );
    }

    public function test_original_order_is_preserved_when_values_are_equal(): void {
        $stories = [
            [
                'id' => 50644817,
                'title' => '1Password Security Incident',
                'points' => 100,
                'comments' => 20,
            ],
            [
                'id' => 50589784,
                'title' => 'SSH is a GUI framework',
                'points' => 100,
                'comments' => 20,
            ],
            [
                'id' => 50645074,
                'title' => 'Britain’s ghost stations',
                'points' => 100,
                'comments' => 20,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);

        $this->assertSame(
            [50644817, 50589784, 50645074],
            array_column($result['short_titles'], 'id'),
        );
    }

    public function test_symbols_and_punctuation_are_handled_when_counting_words(): void {
        $stories = [
            [
                'id' => 50643199,
                'title' => 'In C++, = default and {} are not the same constructor',
                'points' => 91,
                'comments' => 150,
            ],
            [
                'id' => 50644980,
                'title' => 'The polynomial Hirsch conjecture is false [pdf]',
                'points' => 89,
                'comments' => 17,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);

        $this->assertCount(2, $result['long_titles']);
        $this->assertCount(0, $result['short_titles']);
    }

    public function test_assessment_example_is_counted_as_five_words(): void {
        $processor = new StoryProcessor();
        $this->assertSame(5, $processor->countWords('This is - a self-explained example'));
    }

    public function test_empty_or_symbol_only_titles_have_zero_words(): void {
        $processor = new StoryProcessor();
        $this->assertSame(0, $processor->countWords(''));
        $this->assertSame(0, $processor->countWords('--- !!! {}'));
    }

    public function test_long_titles_keep_original_order_when_comment_counts_are_equal(): void {
        $stories = [
            [
                'id' => 1,
                'title' => 'One two three four five six',
                'points' => 10,
                'comments' => 20,
            ],
            [
                'id' => 2,
                'title' => 'Another title with more than five words',
                'points' => 20,
                'comments' => 20,
            ],
        ];

        $result = (new StoryProcessor())->process($stories);
        $this->assertSame([1, 2], array_column($result['long_titles'], 'id'));
    }

    public function test_empty_input_returns_empty_groups(): void {
        $result = (new StoryProcessor())->process([]);

        $this->assertSame([], $result['long_titles']);
        $this->assertSame([], $result['short_titles']);
    }
}
