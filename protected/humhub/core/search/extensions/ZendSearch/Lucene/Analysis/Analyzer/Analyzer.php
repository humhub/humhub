<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Analysis\Analyzer;

use ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface as LuceneAnalyzer;

/**
 * AnalyzerInterface manager.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
class Analyzer
{
    /**
     * The AnalyzerInterface implementation used by default.
     *
     * @var \ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface
     */
    private static $_defaultImpl = null;

    /**
     * Set the default AnalyzerInterface implementation used by indexing code.
     *
     * @param \ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface $analyzer
     */
    public static function setDefault(LuceneAnalyzer $analyzer)
    {
        self::$_defaultImpl = $analyzer;
    }

    /**
     * Return the default AnalyzerInterface implementation used by indexing code.
     *
     * @return \ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface
     */
    public static function getDefault()
    {
        if (self::$_defaultImpl === null) {
            self::$_defaultImpl = new Common\Text\CaseInsensitive();
        }

        return self::$_defaultImpl;
    }
}
