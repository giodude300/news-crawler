<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HackerNewsCrawler {
    private const BASE_URL = 'https://news.ycombinator.com/';

    private const MAX_STORIES = 30;

    public function crawl(): array {
        $response = Http::timeout(15)->retry(2, 500)->get(self::BASE_URL);

        if (!$response->successful()) {
            throw new RuntimeException('Unable to retrieve Hacker News stories.');
        }

        $html = $response->body();
        $previousState = libxml_use_internal_errors(true);

        try {
            // This ensures that DOMDocument interprets the HTML content as UTF-8
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new DOMXPath($document);

            $stories = $xpath->query('//tr[contains(@class, "athing")]');

            if ($stories === false) {
                throw new RuntimeException('Unable to parse Hacker News stories.');
            }

            $results = [];

            foreach ($stories as $story) {
                if (count($results) >= self::MAX_STORIES) {
                    break;
                }

                $id = $story->getAttribute('id');
                $titleNode = $xpath->query('.//span[contains(@class, "titleline")]/a', $story)->item(0);

                if (!$titleNode) {
                    continue;
                }

                $title = trim($titleNode->textContent);
                $subtext = $xpath->query('following-sibling::tr[1]//td[contains(@class, "subtext")]', $story)->item(0);
                $pointsNode = $xpath->query('.//span[contains(@class, "score")]', $subtext)->item(0);
                $commentsNode = $xpath->query('.//a[contains(@href, "item?id=") and contains(., "comment")]', $subtext)->item(0);

                $results[] = [
                    'id' => (int) $id,
                    'title' => $title,
                    'points' => $pointsNode ? (int) filter_var($pointsNode->textContent, FILTER_SANITIZE_NUMBER_INT) : 0,
                    'comments' => $commentsNode ? (int) filter_var($commentsNode->textContent, FILTER_SANITIZE_NUMBER_INT) : 0,
                ];
            }

            return $results;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }
    }
}
