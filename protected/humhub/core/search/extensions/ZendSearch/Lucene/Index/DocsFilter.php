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

/**
 * A Zend_Search_Lucene_Index_DocsFilter is used to filter documents while searching.
 *
 * It may or _may_not_ be used for actual filtering, so it's just a hint that upper query limits
 * search result by specified list.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class DocsFilter
{
    /**
     * Set of segment filters:
     *  array( <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         ...
     *       )
     *
     * @var array
     */
    public $segmentFilters = array();
}

