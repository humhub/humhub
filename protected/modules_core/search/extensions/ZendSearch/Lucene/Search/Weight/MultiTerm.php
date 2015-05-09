<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Weight;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Search\Query;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class MultiTerm extends AbstractWeight
{
    /**
     * IndexReader.
     *
     * @var \ZendSearch\Lucene\SearchIndexInterface
     */
    private $_reader;

    /**
     * The query that this concerns.
     *
     * @var \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    private $_query;

    /**
     * Query terms weights
     * Array of Zend_Search_Lucene_Search_Weight_Term
     *
     * @var array
     */
    private $_weights;


    /**
     * Zend_Search_Lucene_Search_Weight_MultiTerm constructor
     * query - the query that this concerns.
     * reader - index reader
     *
     * @param \ZendSearch\Lucene\Search\Query\AbstractQuery $query
     * @param \ZendSearch\Lucene\SearchIndexInterface             $reader
     */
    public function __construct(Query\AbstractQuery $query, Lucene\SearchIndexInterface $reader)
    {
        $this->_query   = $query;
        $this->_reader  = $reader;
        $this->_weights = array();

        $signs = $query->getSigns();

        foreach ($query->getTerms() as $id => $term) {
            if ($signs === null || $signs[$id] === null || $signs[$id]) {
                $this->_weights[$id] = new Term($term, $query, $reader);
                $query->setWeight($id, $this->_weights[$id]);
            }
        }
    }


    /**
     * The weight for this query
     * Standard Weight::$_value is not used for boolean queries
     *
     * @return float
     */
    public function getValue()
    {
        return $this->_query->getBoost();
    }


    /**
     * The sum of squared weights of contained query clauses.
     *
     * @return float
     */
    public function sumOfSquaredWeights()
    {
        $sum = 0;
        foreach ($this->_weights as $weight) {
            // sum sub weights
            $sum += $weight->sumOfSquaredWeights();
        }

        // boost each sub-weight
        $sum *= $this->_query->getBoost() * $this->_query->getBoost();

        // check for empty query (like '-something -another')
        if ($sum == 0) {
            $sum = 1.0;
        }
        return $sum;
    }


    /**
     * Assigns the query normalization factor to this.
     *
     * @param float $queryNorm
     */
    public function normalize($queryNorm)
    {
        // incorporate boost
        $queryNorm *= $this->_query->getBoost();

        foreach ($this->_weights as $weight) {
            $weight->normalize($queryNorm);
        }
    }
}
