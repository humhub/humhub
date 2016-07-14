<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearchTest\Lucene;

use ZendSearch\Lucene;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class MultiIndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ZendSearch\Lucene\MultiSearcher::find
     * @covers ZendSearch\Lucene\Search\QueryHit::getDocument
     */
    public function testFind()
    {
        $index = new Lucene\MultiSearcher(array(
                Lucene\Lucene::open(__DIR__ . '/_indexSample/_files'),
                Lucene\Lucene::open(__DIR__ . '/_indexSample/_files'),
        ));

        $hits = $index->find('submitting');
        $this->assertEquals(count($hits), 2*3);
        foreach($hits as $hit) {
            $document = $hit->getDocument();
            $this->assertTrue($document instanceof Lucene\Document);
        }
    }
}
