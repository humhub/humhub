<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearchTest\Lucene\Index;

use ZendSearch\Lucene\Index;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class TermInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $termInfo = new Index\TermInfo(0, 1, 2, 3);
        $this->assertTrue($termInfo instanceof Index\TermInfo);

        $this->assertEquals($termInfo->docFreq,      0);
        $this->assertEquals($termInfo->freqPointer,  1);
        $this->assertEquals($termInfo->proxPointer,  2);
        $this->assertEquals($termInfo->skipOffset,   3);
        $this->assertEquals($termInfo->indexPointer, null);

        $termInfo = new Index\TermInfo(0, 1, 2, 3, 4);
        $this->assertEquals($termInfo->indexPointer, 4);
    }
}

