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

use ZendSearch\Lucene\Storage\Directory;
use ZendSearch\Lucene\Index;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class SegmentMergerTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $segmentsDirectory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $outputDirectory   = new Directory\Filesystem(__DIR__ . '/_files');
        $segmentsList = array('_0', '_1', '_2', '_3', '_4');

        $segmentMerger = new Index\SegmentMerger($outputDirectory, 'mergedSegment');

        foreach ($segmentsList as $segmentName) {
            $segmentMerger->addSource(new Index\SegmentInfo($segmentsDirectory, $segmentName, 2));
        }

        $mergedSegment = $segmentMerger->merge();
        $this->assertTrue($mergedSegment instanceof Index\SegmentInfo);
        unset($mergedSegment);

        $mergedFile = $outputDirectory->getFileObject('mergedSegment.cfs');
        $mergedFileData = $mergedFile->readBytes($outputDirectory->fileLength('mergedSegment.cfs'));

        $sampleFile = $outputDirectory->getFileObject('mergedSegment.cfs.sample');
        $sampleFileData = $sampleFile->readBytes($outputDirectory->fileLength('mergedSegment.cfs.sample'));

        $this->assertEquals($mergedFileData, $sampleFileData);

        $outputDirectory->deleteFile('mergedSegment.cfs');
    }
}

