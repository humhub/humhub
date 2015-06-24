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
use ZendSearch\Lucene\Search\Highlighter\HighlighterInterface as Highlighter;
use ZendSearch\Lucene\Search\Weight;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class EmptyResult extends AbstractQuery
{
    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function rewrite(Lucene\SearchIndexInterface $index)
    {
        return $this;
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function optimize(Lucene\SearchIndexInterface $index)
    {
        // "EmptyResult" query is a primitive query and don't need to be optimized
        return $this;
    }

    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @return \ZendSearch\Lucene\Search\Weight\EmptyResultWeight
     */
    public function createWeight(Lucene\SearchIndexInterface $reader)
    {
        return new Weight\EmptyResultWeight();
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
        // Do nothing
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
        return array();
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
        return 0;
    }

    /**
     * Return query terms
     *
     * @return array
     */
    public function getQueryTerms()
    {
        return array();
    }

    /**
     * Query specific matches highlighting
     *
     * @param Highlighter $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Highlighter $highlighter)
    {
        // Do nothing
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        return '<EmptyQuery>';
    }
}
