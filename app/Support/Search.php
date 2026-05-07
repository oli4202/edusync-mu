<?php

namespace App\Support;

/**
 * Search Helper
 * Provides search functionality across models
 */
class Search
{
    /**
     * Sanitize search query
     */
    public static function sanitize(string $query): string
    {
        $query = trim($query);
        $query = preg_replace('/\s+/', ' ', $query); // Remove extra spaces
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        return $query;
    }

    /**
     * Build LIKE clause for SQL
     */
    public static function buildLikeClause(string $query, array $fields): string
    {
        $sanitized = self::sanitize($query);
        
        if (empty($sanitized)) {
            return '1=1';
        }

        $conditions = [];
        foreach ($fields as $field) {
            $conditions[] = "$field LIKE '%$sanitized%'";
        }

        return '(' . implode(' OR ', $conditions) . ')';
    }

    /**
     * Build safe LIKE clause with prepared statements
     */
    public static function buildLikeConditions(string $query, array $fields): array
    {
        $sanitized = self::sanitize($query);
        
        if (empty($sanitized)) {
            return [
                'clause' => '1=1',
                'params' => [],
            ];
        }

        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "$field LIKE ?";
            $params[] = "%$sanitized%";
        }

        return [
            'clause' => '(' . implode(' OR ', $conditions) . ')',
            'params' => $params,
        ];
    }

    /**
     * Highlight search terms in text
     */
    public static function highlight(string $text, string $query, string $highlightClass = 'search-highlight'): string
    {
        $sanitized = self::sanitize($query);
        
        if (empty($sanitized)) {
            return $text;
        }

        $pattern = '/' . preg_quote($sanitized, '/') . '/i';
        return preg_replace(
            $pattern,
            '<span class="' . htmlspecialchars($highlightClass) . '">$0</span>',
            $text
        );
    }

    /**
     * Extract snippet around search term
     */
    public static function snippet(string $text, string $query, int $contextLength = 50): string
    {
        $sanitized = self::sanitize($query);
        
        if (empty($sanitized)) {
            return substr($text, 0, $contextLength) . '...';
        }

        $pos = stripos($text, $sanitized);
        if ($pos === false) {
            return substr($text, 0, $contextLength) . '...';
        }

        $start = max(0, $pos - $contextLength);
        $length = $contextLength * 2 + strlen($sanitized);
        $snippet = substr($text, $start, $length);

        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $length < strlen($text)) {
            $snippet .= '...';
        }

        return $snippet;
    }

    /**
     * Validate search query (prevent abuse)
     */
    public static function isValid(string $query, int $minLength = 2, int $maxLength = 255): bool
    {
        $query = self::sanitize($query);
        $length = strlen($query);
        
        return $length >= $minLength && $length <= $maxLength;
    }

    /**
     * Get common words to exclude from search
     */
    public static function getStopwords(): array
    {
        return [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by',
            'for', 'if', 'in', 'into', 'is', 'it', 'no', 'not', 'of',
            'on', 'or', 'such', 'that', 'the', 'their', 'then', 'there',
            'these', 'they', 'this', 'to', 'was', 'will', 'with',
        ];
    }

    /**
     * Remove stopwords from query
     */
    public static function removeStopwords(string $query): string
    {
        $words = explode(' ', self::sanitize($query));
        $stopwords = self::getStopwords();
        $filtered = array_filter($words, fn($w) => !in_array(strtolower($w), $stopwords));
        return implode(' ', $filtered);
    }
}
