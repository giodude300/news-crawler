<?php

namespace Tests\Unit;

use App\Services\HackerNewsCrawler;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HackerNewsCrawlerTest extends TestCase {
    public function test_stories_are_extracted_correctly(): void {
        Http::fake([
            'https://news.ycombinator.com/' => Http::response($this->getHtml([
                $this->getStoryHtml(50644817, '1Password Security Incident', 880, 378),
                $this->getStoryHtml(50589784, 'SSH is a GUI framework', 91, 48),
            ])),
        ]);

        $stories = (new HackerNewsCrawler())->crawl();
        $this->assertCount(2, $stories);

        $this->assertSame([
            'id' => 50644817,
            'title' => '1Password Security Incident',
            'points' => 880,
            'comments' => 378,
        ], $stories[0]);

        $this->assertSame([
            'id' => 50589784,
            'title' => 'SSH is a GUI framework',
            'points' => 91,
            'comments' => 48,
        ], $stories[1]);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://news.ycombinator.com/' && $request->method() === 'GET';
        });
    }

    public function test_crawler_returns_a_maximum_of_30_stories(): void {
        $html = [];

        for ($i = 1; $i <= 31; $i++) {
            $html[] = $this->getStoryHtml(50600000 + $i, 'Story number ' . $i, 100, 20);
        }

        Http::fake([
            'https://news.ycombinator.com/' => Http::response($this->getHtml($html)),
        ]);

        $stories = (new HackerNewsCrawler())->crawl();

        $this->assertCount(30, $stories);
        $this->assertSame(50600001, $stories[0]['id']);
        $this->assertSame(50600030, $stories[29]['id']);
    }

    public function test_stories_without_points_or_comments_return_zero(): void {
        Http::fake([
            'https://news.ycombinator.com/' => Http::response($this->getHtml([
                $this->getStoryHtml(50641323, 'Hazel (YC W24) Is Hiring a Product Designer', null, null),
            ])),
        ]);

        $stories = (new HackerNewsCrawler())->crawl();

        $this->assertSame(0, $stories[0]['points']);
        $this->assertSame(0, $stories[0]['comments']);
    }

    public function test_stories_without_a_title_are_skipped(): void {
        $html = '
            <tr class="athing" id="50644817">
                <td class="title">
                    <span class="titleline"></span>
                </td>
            </tr>
            <tr>
                <td class="subtext">
                    <span class="score">100 points</span>
                    <a href="item?id=50644817">20 comments</a>
                </td>
            </tr>
        ';

        Http::fake([
            'https://news.ycombinator.com/' => Http::response($this->getHtml([
                $html,
                $this->getStoryHtml(50589784, 'SSH is a GUI framework', 91, 48),
            ])),
        ]);

        $stories = (new HackerNewsCrawler())->crawl();

        $this->assertCount(1, $stories);
        $this->assertSame(50589784, $stories[0]['id']);
    }

    public function test_utf8_titles_are_preserved(): void {
        Http::fake([
            'https://news.ycombinator.com/' => Http::response($this->getHtml([
                $this->getStoryHtml(50646108, 'OMG OMG OMG, I can’t imagine: The magnitude of what’s now happening', 659, 480),
            ])),
        ]);
        $stories = (new HackerNewsCrawler())->crawl();
        $this->assertSame('OMG OMG OMG, I can’t imagine: The magnitude of what’s now happening', $stories[0]['title']);
    }

    public function test_empty_html_returns_an_empty_list(): void {
        Http::fake([ 'https://news.ycombinator.com/' => Http::response($this->getHtml([]))]);
        $this->assertSame([], (new HackerNewsCrawler())->crawl());
    }

    public function test_exception_is_thrown_when_hacker_news_request_fails(): void {
        Http::fake([ 'https://news.ycombinator.com/' => Http::response('Server Error', 500)]);
        $this->expectException(RequestException::class);
        (new HackerNewsCrawler())->crawl();
    }

    private function getHtml(array $stories): string {
        return '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <table>
                        ' . implode('', $stories) . '
                    </table>
                </body>
            </html>';
    }

    private function getStoryHtml(int $id, string $title, ?int $points, ?int $comments): string {
        $score = $points !== null ? '<span class="score">' . $points . ' points</span>' : '';
        $commentLink = $comments !== null ? '<a href="item?id=' . $id . '">' . $comments . ' comments</a>' : '';

        return '
            <tr class="athing" id="' . $id . '">
                <td class="title">
                    <span class="titleline">
                        <a href="https://example.com">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="subtext">
                    ' . $score . '
                    ' . $commentLink . '
                </td>
            </tr>
        ';
    }
}
