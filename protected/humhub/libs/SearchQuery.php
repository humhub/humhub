<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

/**
 * Boolean search query
 *
 * Parses a given search query into relevant terms.
 *
 * Examples:
 * - `Apple Banana`, `+Apple +Banana`
 * - `"Apple pie" "Banana bread"`
 * - `Apple NOT Banana`, `Apple -Banana`
 *
 * @since 1.16
 */
class SearchQuery
{
    /**
     * All terms which are required, without operator or with AND or + operator
     *
     * This is DEFAULT term (for all keyword without operator)
     *
     * Keyword samples how they are stored here:
     *   'Apple' => ['Apple*']
     *   'AND Apple' => ['AND*', 'Apple*']
     *   '+Apple' => ['Apple*']
     *   '"Apple"' => ['Apple']
     *   '+"Apple"' => ['Apple']
     *   '+"Apple Banana"' => ['Apple Banana']
     *   'Apple pie AND Banana' => ['Apple*', 'pie*', 'AND*', 'Banana*']
     *   '"Apple pie" Banana' => ['Apple pie', 'Banana*']
     * @var string[] $terms
     * @readonly
     */
    public array $terms = [];

    /**
     * All terms which should excluded, with NOT or - operator
     *
     * Keyword samples how they are stored here:
     *   'NOT Apple' => ['Apple*']
     *   '-Apple' => ['Apple*']
     *   'NOT "Apple"' => ['Apple']
     *   '-"Apple"' => ['Apple']
     *   '-"Apple Banana"' => ['Apple Banana']
     *   '-Apple -pie NOT Banana' => ['Apple*', 'pie*', 'Banana*']
     *   '-"Apple pie" -Banana' => ['Apple pie', 'Banana*']
     * @var string[] $notTerms
     * @readonly
     */
    public array $notTerms = [];

    /**
     * @param $query string The search query to parse
     */
    public function __construct(string $query)
    {
        foreach ($this->parseQuery($query) as $term) {
            if ($this->isNotTerm($term)) {
                $this->addNotTerm($term);
            } else {
                $this->addTerm($term);
            }
        }
    }

    protected function parseQuery(string $query): array
    {
        preg_match_all('/(?|((?i)NOT )?[\+\-]*"([^"]+)"|(((?i)NOT )?\S+))/', $query, $result);

        return !empty($result[0]) && is_array($result[0]) ? $result[0] : [];
    }

    protected function isNotTerm(string $term): bool
    {
        return str_starts_with($term, '-') || str_starts_with($term, 'NOT ');
    }

    protected function addNotTerm(string $term)
    {
        $term = preg_replace('/^(-*|NOT )/', '', $term);

        if ($term = $this->prepareTerm($term)) {
            $this->notTerms[] = $term;
        }
    }

    protected function addTerm(string $term)
    {
        $term = ltrim($term, '+');

        if ($term = $this->prepareTerm($term)) {
            $this->terms[] = $term;
        }
    }

    /**
     * Prepare a term before using
     *
     * @param string $term
     * @return string|null NULL - if the term must not be used at all
     */
    protected function prepareTerm(string $term): ?string
    {
        if (!$this->isPhrase($term)) {
            // A not quoted term should be searched as wildcard by default
            // The last char '*' is used as flag for wildcard
            $term = rtrim($term, '*"') . '*';
        }

        // Remove all rest quotes around each term
        $term = trim($term, '"');

        // Remove the chars if they stay after first quote like `"---Apple"`, `"++++Banana"`
        $term = ltrim($term, '+-');

        return $term === '' || $term === '*' ? null : $term;
    }

    protected function isPhrase(string $term): bool
    {
        return str_starts_with($term, '"') && str_ends_with($term, '"');
    }
}
