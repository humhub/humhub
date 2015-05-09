<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum;

use ZendSearch\Lucene\Analysis\Analyzer\Common;
use ZendSearch\Lucene\Analysis\TokenFilter;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
class CaseInsensitive extends Common\TextNum
{
    public function __construct()
    {
        $this->addFilter(new TokenFilter\LowerCase());
    }
}

