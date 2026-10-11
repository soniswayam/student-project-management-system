<?php

namespace App\Support;

class SimilarityService
{
    private const STOPWORDS = [
        'a', 'about', 'above', 'after', 'again', 'all', 'also', 'am', 'an', 'and', 'any', 'are', 'as',
        'at', 'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by',
        'can', 'did', 'do', 'does', 'doing', 'down', 'during', 'each', 'few', 'for', 'from', 'further',
        'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'him', 'his', 'how', 'i', 'if',
        'in', 'into', 'is', 'it', 'its', 'itself', 'just', 'me', 'more', 'most', 'my', 'no', 'nor',
        'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'out', 'over',
        'own', 'same', 'she', 'should', 'so', 'some', 'such', 'than', 'that', 'the', 'their', 'theirs',
        'them', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too', 'under',
        'until', 'up', 'very', 'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who',
        'whom', 'why', 'will', 'with', 'would', 'you', 'your', 'yours',
        'project', 'system', 'application', 'website', 'web', 'develop', 'developed', 'development',
        'using', 'user', 'users', 'data', 'software', 'module', 'modules', 'based',
    ];

    public const SHINGLE_SIZE = 3;

    private const MIN_TOKENS_FOR_SHINGLES = self::SHINGLE_SIZE + 1;

    public static function tokenize(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $text = mb_strtolower($text);

        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';

        $tokens = array_values(array_filter(
            preg_split('/\s+/', trim($text)) ?: [],
            fn (string $t) => $t !== ''
                && mb_strlen($t) > 2
                && ! in_array($t, self::STOPWORDS, true)
        ));

        return $tokens;
    }

    public static function shingles(array $tokens, int $size = self::SHINGLE_SIZE): array
    {
        $count = count($tokens);

        if ($count < self::MIN_TOKENS_FOR_SHINGLES) {
            return array_values(array_unique($tokens));
        }

        $shingles = [];
        for ($i = 0; $i <= $count - $size; $i++) {
            $shingles[] = implode(' ', array_slice($tokens, $i, $size));
        }

        return array_values(array_unique($shingles));
    }

    public static function compare(?string $left, ?string $right): array
    {
        $a = self::shingles(self::tokenize($left));
        $b = self::shingles(self::tokenize($right));

        if ($a === [] || $b === []) {
            return ['score' => 0.0, 'shared' => 0, 'phrases' => []];
        }

        $shared = array_values(array_intersect($a, $b));
        $sharedCount = count($shared);

        if ($sharedCount === 0) {
            return ['score' => 0.0, 'shared' => 0, 'phrases' => []];
        }

        $union = count(array_unique(array_merge($a, $b)));
        $jaccard = $union > 0 ? $sharedCount / $union : 0.0;

        $containment = $sharedCount / min(count($a), count($b));

        $score = max($jaccard, $containment) * 100;

        return [
            'score' => round($score, 2),
            'shared' => $sharedCount,
            'phrases' => self::mergePhrases($shared),
        ];
    }

    public static function rank(?string $text, iterable $candidates, float $minScore = 0.0, int $limit = 10): array
    {
        $results = [];

        foreach ($candidates as $candidate) {
            $result = self::compare($text, $candidate['text'] ?? null);

            if ($result['score'] <= 0 || $result['score'] < $minScore) {
                continue;
            }

            $results[] = [
                'id' => $candidate['id'],
                'label' => $candidate['label'] ?? (string) $candidate['id'],
                'score' => $result['score'],
                'shared' => $result['shared'],
                'phrases' => $result['phrases'],
            ];
        }

        usort($results, fn ($x, $y) => $y['score'] <=> $x['score']);

        return array_slice($results, 0, $limit);
    }

    private static function mergePhrases(array $shingles, int $limit = 8): array
    {
        if ($shingles === []) {
            return [];
        }

        sort($shingles);
        $merged = [];
        $current = array_shift($shingles);

        foreach ($shingles as $shingle) {
            $currentWords = explode(' ', $current);
            $words = explode(' ', $shingle);

            $tail = array_slice($currentWords, -(self::SHINGLE_SIZE - 1));
            $head = array_slice($words, 0, self::SHINGLE_SIZE - 1);

            if ($tail === $head) {
                $current .= ' '.end($words);

                continue;
            }

            $merged[] = $current;
            $current = $shingle;
        }

        $merged[] = $current;

        usort($merged, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return array_slice($merged, 0, $limit);
    }
}
