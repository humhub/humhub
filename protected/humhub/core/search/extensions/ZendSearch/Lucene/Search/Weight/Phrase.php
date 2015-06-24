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
class Phrase extends AbstractWeight
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
     * @var \ZendSearch\Lucene\Search\Query\Phrase
     */
    private $_query;

    /**
     * Score factor
     *
     * @var float
     */
    private $_idf;

    /**
     * Zend_Search_Lucene_Search_Weight_Phrase constructor
     *
     * @param \ZendSearch\Lucene\Search\Query\Phrase $query
     * @param \ZendSearch\Lucene\SearchIndexInterface      $reader
     */
    public function __construct(Query\Phrase $query, Lucene\SearchIndexInterface $reader)
    {
        $this->_query  = $query;
        $this->_reader = $reader;
    }

    /**
     * The sum of squared weights of contained query clauses.
     *
     * @return float
     */
    public function sumOfSquaredWeights()
    {
        // compute idf
        $this->_idf = $this->_reader->getSimilarity()->idf($this->_query->getTerms(), $this->_reader);

        // compute query weight
        $this->_queryWeight = $this->_idf * $this->_query->getBoost();

        // square it
        return $this->_queryWeight * $this->_queryWeight;
    }


    /**
     * Assigns the query normalization factor to this.
     *
     * @param float $queryNorm
     */
    public function normalize($queryNorm)
    {
        $this->_queryNorm = $queryNorm;

        // normalize query weight
        $this->_queryWeight *= $queryNorm;

        // idf for documents
        $this->_value = $this->_queryWeight * $this->_idf;
    }
}
