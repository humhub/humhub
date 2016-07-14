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
 * Token filter that removes short words. What is short word can be configured with constructor.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
class ShortWords implements TokenFilterInterface
{
    /**
     * Minimum allowed term length
     * @var integer
     */
    private $length;

    /**
     * Constructs new instance of this filter.
     *
     * @param integer $short  minimum allowed length of term which passes this filter (default 2)
     */
    public function __construct($length = 2)
    {
        $this->length = $length;
    }

    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param \ZendSearch\Lucene\Analysis\Token $srcToken
     * @return \ZendSearch\Lucene\Analysis\Token
     */
    public function normalize(Token $srcToken)
    {
        if (strlen($srcToken->getTermText()) < $this->length) {
            return null;
        } else {
            return $srcToken;
        }
    }
}

