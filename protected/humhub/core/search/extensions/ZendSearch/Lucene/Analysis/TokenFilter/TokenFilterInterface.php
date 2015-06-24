<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Analysis\TokenFilter;

use ZendSearch\Lucene\Analysis\Token;

/**
 * Token filter converts (normalizes) Token ore removes it from a token stream.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
interface TokenFilterInterface
{
    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param \ZendSearch\Lucene\Analysis\Token $srcToken
     * @return \ZendSearch\Lucene\Analysis\Token
     */
    public function normalize(Token $srcToken);
}
