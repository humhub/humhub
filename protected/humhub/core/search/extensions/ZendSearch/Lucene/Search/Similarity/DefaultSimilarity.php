<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Similarity;

use ZendSearch\Lucene\Search\Similarity\AbstractSimilarity;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class DefaultSimilarity extends AbstractSimilarity
{

    /**
     * Implemented as '1/sqrt(numTerms)'.
     *
     * @param string $fieldName
     * @param integer $numTerms
     * @return float
     */
    public function lengthNorm($fieldName, $numTerms)
    {
        if ($numTerms == 0) {
            return 1E10;
        }

        return 1.0/sqrt($numTerms);
    }

    /**
     * Implemented as '1/sqrt(sumOfSquaredWeights)'.
     *
     * @param float $sumOfSquaredWeights
     * @return float
     */
    public function queryNorm($sumOfSquaredWeights)
    {
        return 1.0/sqrt($sumOfSquaredWeights);
    }

    /**
     * Implemented as 'sqrt(freq)'.
     *
     * @param float $freq
     * @return float
     */
    public function tf($freq)
    {
        return sqrt($freq);
    }

    /**
     * Implemented as '1/(distance + 1)'.
     *
     * @param integer $distance
     * @return float
     */
    public function sloppyFreq($distance)
    {
        return 1.0/($distance + 1);
    }

    /**
     * Implemented as 'log(numDocs/(docFreq+1)) + 1'.
     *
     * @param integer $docFreq
     * @param integer $numDocs
     * @return float
     */
    public function idfFreq($docFreq, $numDocs)
    {
        return log($numDocs/(float)($docFreq+1)) + 1.0;
    }

    /**
     * Implemented as 'overlap/maxOverlap'.
     *
     * @param integer $overlap
     * @param integer $maxOverlap
     * @return float
     */
    public function coord($overlap, $maxOverlap)
    {
        return $overlap/(float)$maxOverlap;
    }
}
