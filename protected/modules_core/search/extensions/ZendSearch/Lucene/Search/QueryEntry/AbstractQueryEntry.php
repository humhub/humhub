<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\QueryEntry;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
abstract class AbstractQueryEntry
{
    /**
     * Query entry boost factor
     *
     * @var float
     */
    protected $_boost = 1.0;


    /**
     * Process modifier ('~')
     *
     * @param mixed $parameter
     */
    abstract public function processFuzzyProximityModifier($parameter = null);


    /**
     * Transform entry to a subquery
     *
     * @param string $encoding
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    abstract public function getQuery($encoding);

    /**
     * Boost query entry
     *
     * @param float $boostFactor
     */
    public function boost($boostFactor)
    {
        $this->_boost *= $boostFactor;
    }
}
