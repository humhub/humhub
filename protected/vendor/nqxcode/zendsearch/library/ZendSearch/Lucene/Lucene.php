<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene;

use ZendSearch\Lucene\Exception\UnsupportedMethodCallException;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 */
class Lucene
{
    /**
     * Default field name for search
     *
     * Null means search through all fields
     *
     * @var string
     */
    private static $_defaultSearchField = null;

    /**
     * Result set limit
     *
     * 0 means no limit
     *
     * @var integer
     */
    private static $_resultSetLimit = 0;

    /**
     * Terms per query limit
     *
     * 0 means no limit
     *
     * @var integer
     */
    private static $_termsPerQueryLimit = 1024;

    /**
     * Create index
     *
     * @param mixed $directory
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public static function create($directory)
    {
        return new Index($directory, true);
    }

    /**
     * Open index
     *
     * @param mixed $directory
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public static function open($directory)
    {
        return new Index($directory, false);
    }

    /**
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     */
    public function __construct()
    {
        throw new UnsupportedMethodCallException('\ZendSearch\Lucene class is the only container for static methods. Use Lucene::open() or Lucene::create() methods.');
    }

    /**
     * Set default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * Default value is null
     *
     * @param string $fieldName
     */
    public static function setDefaultSearchField($fieldName)
    {
        self::$_defaultSearchField = $fieldName;
    }

    /**
     * Get default search field.
     *
     * Null means, that search is performed through all fields by default
     *
     * @return string
     */
    public static function getDefaultSearchField()
    {
        return self::$_defaultSearchField;
    }

    /**
     * Set result set limit.
     *
     * 0 (default) means no limit
     *
     * @param integer $limit
     */
    public static function setResultSetLimit($limit)
    {
        self::$_resultSetLimit = $limit;
    }

    /**
     * Get result set limit.
     *
     * 0 means no limit
     *
     * @return integer
     */
    public static function getResultSetLimit()
    {
        return self::$_resultSetLimit;
    }

    /**
     * Set terms per query limit.
     *
     * 0 means no limit
     *
     * @param integer $limit
     */
    public static function setTermsPerQueryLimit($limit)
    {
        self::$_termsPerQueryLimit = $limit;
    }

    /**
     * Get result set limit.
     *
     * 0 (default) means no limit
     *
     * @return integer
     */
    public static function getTermsPerQueryLimit()
    {
        return self::$_termsPerQueryLimit;
    }
}
