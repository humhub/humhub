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
 * - `Apple Banana`, `Apple OR Banana`
 * - `Apple AND Banana`, `+Apple +Banana`
 * - `Apple AND Banana OR Grape`, `+Apple +Banana Grape`
 * - `"Apple pie" "Banana bread"`
 * - `Apple NOT Banana`, `Apple -Banana`
 *
 *
 * @since 1.16
 */
class SearchQuery
{
    /**
     * @var string[] All terms without operator
     * @readonly
     */
    public array $orTerms;

    /**
     * @var string[] All terms which are required, with AND or + operator
     * @readonly
     */
    public array $andTerms;

    /**
     * @var string[] All terms which should excluded, with NOT or - operator
     * @readonly
     */
    public array $notTerms;

    /**
     * @param $query string The search query to parse
     */
    public function __construct(string $query)
    {
        $notTerms = [];
        $orTerms = [];
        $andTerms = [];

        preg_match_all(
            '/(?|((?i)AND )?((?i)OR )?((?i)NOT )?[\+\-]?"([^"]+)"|(((?i)AND )?((?i)OR )?((?i)NOT )?\S+))/',
            $query,
            $result,
            PREG_PATTERN_ORDER,
        );

        if (!empty($result[0]) && is_array($result[0])) {
            foreach ($result[0] as $i => $term) {
                if (!preg_match('/^(\+|\-|AND |NOT )?".+"$/', $term)) {
                    // A not quoted term should be searched with mask by default
                    $term = rtrim($term, '*"') . '*';
                }

                // Remove quotation marks
                $term = str_replace('"', '', $term);

                if (str_starts_with($term, 'OR ')) {
                    $orTerms[] = preg_replace('/^((?i)OR )?/', '', $term);
                } elseif (str_starts_with($term, '-') || str_starts_with($term, 'NOT ')) {
                    $notTerms[] = preg_replace('/^\-?((?i)NOT )?/', '', $term);
                } else {
                    // Use AND operator by default

                    /**
                     * Special Case: In search queries like "Apple AND Banana", Apple should
                     * become a `AND` term too.
                     */
                    if ($i === 1 && str_starts_with($term, 'AND ') && count($orTerms) === 1) {
                        $andTerms = $orTerms;
                        $orTerms = [];
                    }

                    $andTerms[] = preg_replace('/^\+?((?i)AND )?/', '', $term);
                }
            }
        }

        $this->notTerms = array_filter($notTerms, [$this, 'filterEmptyTerms']);
        $this->orTerms = array_filter($orTerms, [$this, 'filterEmptyTerms']);
        $this->andTerms = array_filter($andTerms, [$this, 'filterEmptyTerms']);
    }

    private function filterEmptyTerms($term): bool
    {
        return !empty($term) && $term !== '*';
    }
}
