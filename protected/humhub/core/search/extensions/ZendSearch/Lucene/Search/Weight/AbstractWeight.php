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

/**
 * Calculate query weights and build query scorers.
 *
 * A AbstractWeight is constructed by a query Query->createWeight().
 * The sumOfSquaredWeights() method is then called on the top-level
 * query to compute the query normalization factor Similarity->queryNorm(float).
 * This factor is then passed to normalize(float).  At this point the weighting
 * is complete.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
abstract class AbstractWeight
{
    /**
     * Normalization factor.
     * This value is stored only for query expanation purpose and not used in any other place
     *
     * @var float
     */
    protected $_queryNorm;

    /**
     * AbstractWeight value
     *
     * AbstractWeight value may be initialized in sumOfSquaredWeights() or normalize()
     * because they both are invoked either in Query::_initWeight (for top-level query) or
     * in corresponding methods of parent query's weights
     *
     * @var float
     */
    protected $_value;


    /**
     * The weight for this query.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * The sum of squared weights of contained query clauses.
     *
     * @return float
     */
    abstract public function sumOfSquaredWeights();

    /**
     * Assigns the query normalization factor to this.
     *
     * @param $norm
     */
    abstract public function normalize($norm);
}
