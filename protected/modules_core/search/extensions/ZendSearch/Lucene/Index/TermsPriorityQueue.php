<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Index;

use ZendSearch\Lucene;

/** @todo !!!!!! convert to SPL class usage */

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class TermsPriorityQueue extends Lucene\AbstractPriorityQueue
{
    /**
     * Compare elements
     *
     * Returns true, if $termsStream1 is "less" than $termsStream2; else otherwise
     *
     * @param mixed $termsStream1
     * @param mixed $termsStream2
     * @return boolean
     */
    protected function _less($termsStream1, $termsStream2)
    {
        return strcmp($termsStream1->currentTerm()->key(), $termsStream2->currentTerm()->key()) < 0;
    }
}
