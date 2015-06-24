<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Query;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Exception\InvalidArgumentException;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Search\Highlighter\HighlighterInterface as Highlighter;
use ZendSearch\Lucene\Search\Weight;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class MultiTerm extends AbstractQuery
{

    /**
     * Terms to find.
     * Array of Zend_Search_Lucene_Index_Term
     *
     * @var array
     */
    private $_terms = array();

    /**
     * Term signs.
     * If true then term is required.
     * If false then term is prohibited.
     * If null then term is neither prohibited, nor required
     *
     * If array is null then all terms are required
     *
     * @var array
     */
    private $_signs;

    /**
     * Result vector.
     *
     * @var array
     */
    private $_resVector = null;

    /**
     * Terms positions vectors.
     * Array of Arrays:
     * term1Id => (docId => freq, ...)
     * term2Id => (docId => freq, ...)
     *
     * @var array
     */
    private $_termsFreqs = array();


    /**
     * A score factor based on the fraction of all query terms
     * that a document contains.
     * float for conjunction queries
     * array of float for non conjunction queries
     *
     * @var mixed
     */
    private $_coord = null;


    /**
     * Terms weights
     * array of Zend_Search_Lucene_Search_Weight
     *
     * @var array
     */
    private $_weights = array();


    /**
     * Class constructor.  Create a new multi-term query object.
     *
     * if $signs array is omitted then all terms are required
     * it differs from addTerm() behavior, but should never be used
     *
     * @param array $terms    Array of \ZendSearch\Lucene\Index\Term objects
     * @param array $signs    Array of signs.  Sign is boolean|null.
     * @throws \ZendSearch\Lucene\Exception\InvalidArgumentException
     */
    public function __construct($terms = null, $signs = null)
    {
        if (is_array($terms)) {
            if (count($terms) > Lucene\Lucene::getTermsPerQueryLimit()) {
                throw new InvalidArgumentException('Terms per query limit is reached.');
            }

            $this->_terms = $terms;

            $this->_signs = null;
            // Check if all terms are required
            if (is_array($signs)) {
                foreach ($signs as $sign ) {
                    if ($sign !== true) {
                        $this->_signs = $signs;
                        break;
                    }
                }
            }
        }
    }


    /**
     * Add a $term (Zend_Search_Lucene_Index_Term) to this query.
     *
     * The sign is specified as:
     *     TRUE  - term is required
     *     FALSE - term is prohibited
     *     NULL  - term is neither prohibited, nor required
     *
     * @param  \ZendSearch\Lucene\Index\Term $term
     * @param  boolean|null $sign
     * @return void
     */
    public function addTerm(Index\Term $term, $sign = null)
    {
        if ($sign !== true || $this->_signs !== null) {       // Skip, if all terms are required
            if ($this->_signs === null) {                     // Check, If all previous terms are required
                $this->_signs = array();
                foreach ($this->_terms as $prevTerm) {
                    $this->_signs[] = true;
                }
            }
            $this->_signs[] = $sign;
        }

        $this->_terms[] = $term;
    }


    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function rewrite(Lucene\SearchIndexInterface $index)
    {
        if (count($this->_terms) == 0) {
            return new EmptyResult();
        }

        // Check, that all fields are qualified
        $allQualified = true;
        foreach ($this->_terms as $term) {
            if ($term->field === null) {
                $allQualified = false;
                break;
            }
        }

        if ($allQualified) {
            return $this;
        } else {
            /** transform multiterm query to boolean and apply rewrite() method to subqueries. */
            $query = new Boolean();
            $query->setBoost($this->getBoost());

            foreach ($this->_terms as $termId => $term) {
                $subquery = new Term($term);

                $query->addSubquery($subquery->rewrite($index),
                                    ($this->_signs === null)?  true : $this->_signs[$termId]);
            }

            return $query;
        }
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function optimize(Lucene\SearchIndexInterface $index)
    {
        $terms = $this->_terms;
        $signs = $this->_signs;

        foreach ($terms as $id => $term) {
            if (!$index->hasTerm($term)) {
                if ($signs === null  ||  $signs[$id] === true) {
                    // Term is required
                    return new EmptyResult();
                } else {
                    // Term is optional or prohibited
                    // Remove it from terms and signs list
                    unset($terms[$id]);
                    unset($signs[$id]);
                }
            }
        }

        // Check if all presented terms are prohibited
        $allProhibited = true;
        if ($signs === null) {
            $allProhibited = false;
        } else {
            foreach ($signs as $sign) {
                if ($sign !== false) {
                    $allProhibited = false;
                    break;
                }
            }
        }
        if ($allProhibited) {
            return new EmptyResult();
        }

        /**
         * @todo make an optimization for repeated terms
         * (they may have different signs)
         */

        if (count($terms) == 1) {
            // It's already checked, that it's not a prohibited term

            // It's one term query with one required or optional element
            $optimizedQuery = new Term(reset($terms));
            $optimizedQuery->setBoost($this->getBoost());

            return $optimizedQuery;
        }

        if (count($terms) == 0) {
            return new EmptyResult();
        }

        $optimizedQuery = new MultiTerm($terms, $signs);
        $optimizedQuery->setBoost($this->getBoost());
        return $optimizedQuery;
    }


    /**
     * Returns query term
     *
     * @return array
     */
    public function getTerms()
    {
        return $this->_terms;
    }


    /**
     * Return terms signs
     *
     * @return array
     */
    public function getSigns()
    {
        return $this->_signs;
    }


    /**
     * Set weight for specified term
     *
     * @param integer $num
     * @param \ZendSearch\Lucene\Search\Weight\Term $weight
     */
    public function setWeight($num, $weight)
    {
        $this->_weights[$num] = $weight;
    }


    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @return \ZendSearch\Lucene\Search\Weight\MultiTerm
     */
    public function createWeight(Lucene\SearchIndexInterface $reader)
    {
        $this->_weight = new Weight\MultiTerm($this, $reader);
        return $this->_weight;
    }


    /**
     * Calculate result vector for Conjunction query
     * (like '+something +another')
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     */
    private function _calculateConjunctionResult(Lucene\SearchIndexInterface $reader)
    {
        $this->_resVector = null;

        if (count($this->_terms) == 0) {
            $this->_resVector = array();
        }

        // Order terms by selectivity
        $docFreqs = array();
        $ids      = array();
        foreach ($this->_terms as $id => $term) {
            $docFreqs[] = $reader->docFreq($term);
            $ids[]      = $id; // Used to keep original order for terms with the same selectivity and omit terms comparison
        }
        array_multisort($docFreqs, SORT_ASC, SORT_NUMERIC,
                        $ids,      SORT_ASC, SORT_NUMERIC,
                        $this->_terms);

        $docsFilter = new Lucene\Index\DocsFilter();
        foreach ($this->_terms as $termId => $term) {
            $termDocs = $reader->termDocs($term, $docsFilter);
        }
        // Treat last retrieved docs vector as a result set
        // (filter collects data for other terms)
        $this->_resVector = array_flip($termDocs);

        foreach ($this->_terms as $termId => $term) {
            $this->_termsFreqs[$termId] = $reader->termFreqs($term, $docsFilter);
        }

        // ksort($this->_resVector, SORT_NUMERIC);
        // Docs are returned ordered. Used algorithms doesn't change elements order.
    }


    /**
     * Calculate result vector for non Conjunction query
     * (like '+something -another')
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     */
    private function _calculateNonConjunctionResult(Lucene\SearchIndexInterface $reader)
    {
        $requiredVectors      = array();
        $requiredVectorsSizes = array();
        $requiredVectorsIds   = array(); // is used to prevent arrays comparison

        $optional   = array();
        $prohibited = array();

        foreach ($this->_terms as $termId => $term) {
            $termDocs = array_flip($reader->termDocs($term));

            if ($this->_signs[$termId] === true) {
                // required
                $requiredVectors[]      = $termDocs;
                $requiredVectorsSizes[] = count($termDocs);
                $requiredVectorsIds[]   = $termId;
            } elseif ($this->_signs[$termId] === false) {
                // prohibited
                // array union
                $prohibited += $termDocs;
            } else {
                // neither required, nor prohibited
                // array union
                $optional += $termDocs;
            }

            $this->_termsFreqs[$termId] = $reader->termFreqs($term);
        }

        // sort resvectors in order of subquery cardinality increasing
        array_multisort($requiredVectorsSizes, SORT_ASC, SORT_NUMERIC,
                        $requiredVectorsIds,   SORT_ASC, SORT_NUMERIC,
                        $requiredVectors);

        $required = null;
        foreach ($requiredVectors as $nextResVector) {
            if($required === null) {
                $required = $nextResVector;
            } else {
                //$required = array_intersect_key($required, $nextResVector);

                /**
                 * This code is used as workaround for array_intersect_key() slowness problem.
                 */
                $updatedVector = array();
                foreach ($required as $id => $value) {
                    if (isset($nextResVector[$id])) {
                        $updatedVector[$id] = $value;
                    }
                }
                $required = $updatedVector;
            }

            if (count($required) == 0) {
                // Empty result set, we don't need to check other terms
                break;
            }
        }

        if ($required !== null) {
            $this->_resVector = $required;
        } else {
            $this->_resVector = $optional;
        }

        if (count($prohibited) != 0) {
            // $this->_resVector = array_diff_key($this->_resVector, $prohibited);

            /**
             * This code is used as workaround for array_diff_key() slowness problem.
             */
            if (count($this->_resVector) < count($prohibited)) {
                $updatedVector = $this->_resVector;
                foreach ($this->_resVector as $id => $value) {
                    if (isset($prohibited[$id])) {
                        unset($updatedVector[$id]);
                    }
                }
                $this->_resVector = $updatedVector;
            } else {
                $updatedVector = $this->_resVector;
                foreach ($prohibited as $id => $value) {
                    unset($updatedVector[$id]);
                }
                $this->_resVector = $updatedVector;
            }
        }

        ksort($this->_resVector, SORT_NUMERIC);
    }


    /**
     * Score calculator for conjunction queries (all terms are required)
     *
     * @param integer $docId
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @return float
     */
    public function _conjunctionScore($docId, Lucene\SearchIndexInterface $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = $reader->getSimilarity()->coord(count($this->_terms),
                                                            count($this->_terms) );
        }

        $score = 0.0;

        foreach ($this->_terms as $termId => $term) {
            /**
             * We don't need to check that term freq is not 0
             * Score calculation is performed only for matched docs
             */
            $score += $reader->getSimilarity()->tf($this->_termsFreqs[$termId][$docId]) *
                      $this->_weights[$termId]->getValue() *
                      $reader->norm($docId, $term->field);
        }

        return $score * $this->_coord * $this->getBoost();
    }


    /**
     * Score calculator for non conjunction queries (not all terms are required)
     *
     * @param integer $docId
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @return float
     */
    public function _nonConjunctionScore($docId, $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = array();

            $maxCoord = 0;
            foreach ($this->_signs as $sign) {
                if ($sign !== false /* not prohibited */) {
                    $maxCoord++;
                }
            }

            for ($count = 0; $count <= $maxCoord; $count++) {
                $this->_coord[$count] = $reader->getSimilarity()->coord($count, $maxCoord);
            }
        }

        $score = 0.0;
        $matchedTerms = 0;
        foreach ($this->_terms as $termId=>$term) {
            // Check if term is
            if ($this->_signs[$termId] !== false &&        // not prohibited
                isset($this->_termsFreqs[$termId][$docId]) // matched
               ) {
                $matchedTerms++;

                /**
                 * We don't need to check that term freq is not 0
                 * Score calculation is performed only for matched docs
                 */
                $score +=
                      $reader->getSimilarity()->tf($this->_termsFreqs[$termId][$docId]) *
                      $this->_weights[$termId]->getValue() *
                      $reader->norm($docId, $term->field);
            }
        }

        return $score * $this->_coord[$matchedTerms] * $this->getBoost();
    }

    /**
     * Execute query in context of index reader
     * It also initializes necessary internal structures
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     */
    public function execute(Lucene\SearchIndexInterface $reader, $docsFilter = null)
    {
        if ($this->_signs === null) {
            $this->_calculateConjunctionResult($reader);
        } else {
            $this->_calculateNonConjunctionResult($reader);
        }

        // Initialize weight if it's not done yet
        $this->_initWeight($reader);
    }

    /**
     * Get document ids likely matching the query
     *
     * It's an array with document ids as keys (performance considerations)
     *
     * @return array
     */
    public function matchedDocs()
    {
        return $this->_resVector;
    }

    /**
     * Score specified document
     *
     * @param integer $docId
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @return float
     */
    public function score($docId, Lucene\SearchIndexInterface $reader)
    {
        if (isset($this->_resVector[$docId])) {
            if ($this->_signs === null) {
                return $this->_conjunctionScore($docId, $reader);
            } else {
                return $this->_nonConjunctionScore($docId, $reader);
            }
        } else {
            return 0;
        }
    }

    /**
     * Return query terms
     *
     * @return array
     */
    public function getQueryTerms()
    {
        if ($this->_signs === null) {
            return $this->_terms;
        }

        $terms = array();

        foreach ($this->_signs as $id => $sign) {
            if ($sign !== false) {
                $terms[] = $this->_terms[$id];
            }
        }

        return $terms;
    }

    /**
     * Query specific matches highlighting
     *
     * @param Highlighter $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Highlighter $highlighter)
    {
        $words = array();

        if ($this->_signs === null) {
            foreach ($this->_terms as $term) {
                $words[] = $term->text;
            }
        } else {
            foreach ($this->_signs as $id => $sign) {
                if ($sign !== false) {
                    $words[] = $this->_terms[$id]->text;
                }
            }
        }

        $highlighter->highlight($words);
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping

        $query = '';

        foreach ($this->_terms as $id => $term) {
            if ($id != 0) {
                $query .= ' ';
            }

            if ($this->_signs === null || $this->_signs[$id] === true) {
                $query .= '+';
            } elseif ($this->_signs[$id] === false) {
                $query .= '-';
            }

            if ($term->field !== null) {
                $query .= $term->field . ':';
            }
            $query .= $term->text;
        }

        if ($this->getBoost() != 1) {
            $query = '(' . $query . ')^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}
