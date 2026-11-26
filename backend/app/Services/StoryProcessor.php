<?php

namespace App\Services;

class StoryProcessor {
    public function process(array $stories): array {
        $longTitles = [];
        $shortTitles = [];

        foreach ($stories as $story) {
            $wordCount = $this->countWords($story['title']);

            if ($wordCount > 5) {
                $longTitles[] = $story;
                continue;
            }

            $shortTitles[] = $story;
        }

        usort($longTitles, function ($first, $second) {
            return $second['comments'] <=> $first['comments'];
        });

        usort($shortTitles, function ($first, $second) {
            return $second['points'] <=> $first['points'];
        });

        return [
            'long_titles' => $longTitles,
            'short_titles' => $shortTitles,
        ];
    }

    public function countWords(string $title): int {
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', '', $title);
        $words = preg_split('/\s+/', trim($title));
        return count(array_filter($words));
    }
}
