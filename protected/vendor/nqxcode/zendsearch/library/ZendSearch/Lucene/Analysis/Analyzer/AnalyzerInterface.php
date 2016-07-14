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

/**
 * An AnalyzerInterface is used to analyze text.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
interface AnalyzerInterface
{
    /**
     * Tokenize text to terms
     * Returns array of ZendSearch\Lucene\Analysis\Token objects
     *
     * Tokens are returned in UTF-8 (internal Zend_Search_Lucene encoding)
     *
     * @param string $data
     * @return array
     */
    public function tokenize($data, $encoding = '');

    /**
     * Tokenization stream API
     * Set input
     *
     * @param string $data
     */
    public function setInput($data, $encoding = '');

    /**
     * Reset token stream
     */
    public function reset();

    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * Tokens are returned in UTF-8 (internal Zend_Search_Lucene encoding)
     *
     * @return \ZendSearch\Lucene\Analysis\Token|null
     */
    public function nextToken();
}
