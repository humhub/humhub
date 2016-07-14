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
use ZendSearch\Lucene\Storage\File;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class SegmentInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertTrue($segmentInfo instanceof Index\SegmentInfo);
    }

    public function testOpenCompoundFile()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $file1 = $segmentInfo->openCompoundFile('.fnm');
        $this->assertTrue($file1 instanceof File\FileInterface);

        $file2 = $segmentInfo->openCompoundFile('.tii');
        $file3 = $segmentInfo->openCompoundFile('.tii');
        $file4 = $segmentInfo->openCompoundFile('.tii', false);

        $this->assertTrue($file2 instanceof File\FileInterface);
        $this->assertTrue($file2 === $file3);
        $this->assertTrue($file4 instanceof File\FileInterface);
        $this->assertTrue($file2 !== $file4);
    }


    public function testCompoundFileLength()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertEquals($segmentInfo->compoundFileLength('.tii'), 58);
    }

    public function testGetFieldNum()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertEquals($segmentInfo->getFieldNum('contents'), 2);
        $this->assertEquals($segmentInfo->getFieldNum('non-presented-field'), -1);
    }

    public function testGetField()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $fieldInfo = $segmentInfo->getField(2);

        $this->assertEquals($fieldInfo->name, 'contents');
        $this->assertTrue((boolean)$fieldInfo->isIndexed);
        $this->assertEquals($fieldInfo->number, 2);
        $this->assertFalse((boolean)$fieldInfo->storeTermVector);
    }

    public function testGetFields()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertTrue($segmentInfo->getFields() == array('path' => 'path', 'modified' => 'modified', 'contents' => 'contents'));
        $this->assertTrue($segmentInfo->getFields(true) == array('path' => 'path', 'modified' => 'modified', 'contents' => 'contents'));
    }

    public function testGetFieldInfos()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $fieldInfos = $segmentInfo->getFieldInfos();

        $this->assertEquals($fieldInfos[0]->name, 'path');
        $this->assertTrue((boolean)$fieldInfos[0]->isIndexed);
        $this->assertEquals($fieldInfos[0]->number, 0);
        $this->assertFalse((boolean)$fieldInfos[0]->storeTermVector);

        $this->assertEquals($fieldInfos[1]->name, 'modified');
        $this->assertTrue((boolean)$fieldInfos[1]->isIndexed);
        $this->assertEquals($fieldInfos[1]->number, 1);
        $this->assertFalse((boolean)$fieldInfos[1]->storeTermVector);

        $this->assertEquals($fieldInfos[2]->name, 'contents');
        $this->assertTrue((boolean)$fieldInfos[2]->isIndexed);
        $this->assertEquals($fieldInfos[2]->number, 2);
        $this->assertFalse((boolean)$fieldInfos[2]->storeTermVector);
    }

    public function testCount()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertEquals($segmentInfo->count(), 2);
    }

    public function testNumDocs()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_3', 2);

        $this->assertEquals($segmentInfo->count(), 2);
        $this->assertEquals($segmentInfo->numDocs(), 1);
    }

    public function testGetName()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertEquals($segmentInfo->getName(), '_1');
    }

    public function testGetTermInfo()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $termInfo = $segmentInfo->getTermInfo(new Index\Term('apart', 'contents'));

        $this->assertEquals($termInfo->docFreq, 1);
        $this->assertEquals($termInfo->freqPointer, 29);
        $this->assertEquals($termInfo->proxPointer, 119);
        $this->assertEquals($termInfo->skipOffset, 0);
        $this->assertEquals($termInfo->indexPointer, null);

        $termInfo1 = $segmentInfo->getTermInfo(new Index\Term('apart', 'contents'));
        // test for requesting cached information
        $this->assertTrue($termInfo === $termInfo1);

        // request for non-existing term
        $this->assertTrue($segmentInfo->getTermInfo(new Index\Term('nonusedterm', 'contents')) === null);
    }

    public function testTermFreqs()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $termPositions = $segmentInfo->termFreqs(new Index\Term('bgcolor', 'contents'));
        $this->assertTrue($termPositions == array(0 => 3, 1 => 1));

        $termPositions = $segmentInfo->termFreqs(new Index\Term('bgcolor', 'contents'), 10);
        $this->assertTrue($termPositions == array(10 => 3, 11 => 1));
    }

    public function testTermPositions()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $termPositions = $segmentInfo->termPositions(new Index\Term('bgcolor', 'contents'));
        $this->assertTrue($termPositions == array(0 => array(69, 239, 370),
                                                  1 => array(58)
                                                 ));

        $termPositions = $segmentInfo->termPositions(new Index\Term('bgcolor', 'contents'), 10);
        $this->assertTrue($termPositions == array(10 => array(69, 239, 370),
                                                  11 => array(58)
                                                 ));
    }

    public function testNorm()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertTrue(abs($segmentInfo->norm(1, 'contents') - 0.0546875) < 0.000001);
    }

    public function testNormVector()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');
        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);

        $this->assertEquals($segmentInfo->normVector('contents'), "\x69\x6B");
    }

    public function testHasDeletions()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2);
        $this->assertFalse($segmentInfo->hasDeletions());

        $segmentInfo1 = new Index\SegmentInfo($directory, '_3', 2);
        $this->assertTrue($segmentInfo1->hasDeletions());
    }

    public function testDelete()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_1', 2, 0 /* search for _1.del file */);
        $this->assertFalse($segmentInfo->hasDeletions());

        $segmentInfo->delete(0);
        $this->assertTrue($segmentInfo->hasDeletions());
        $delGen = $segmentInfo->getDelGen();
        // don't write changes
        unset($segmentInfo);

        $segmentInfo1 = new Index\SegmentInfo($directory, '_1', 2, $delGen);
        // Changes wasn't written, segment still has no deletions
        $this->assertFalse($segmentInfo1->hasDeletions());

        $segmentInfo1->delete(0);
        $segmentInfo1->writeChanges();
        $delGen = $segmentInfo1->getDelGen();
        unset($segmentInfo1);

        $segmentInfo2 = new Index\SegmentInfo($directory, '_1', 2, $delGen);
        $this->assertTrue($segmentInfo2->hasDeletions());
        unset($segmentInfo2);

        $directory->deleteFile('_1_' . base_convert($delGen, 10, 36) . '.del');

        $segmentInfo3 = new Index\SegmentInfo($directory, '_1', 2, -1 /* no detetions file */);
        $this->assertFalse($segmentInfo3->hasDeletions());
    }

    public function testIsDeleted()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_2', 2);
        $this->assertFalse($segmentInfo->isDeleted(0));

        $segmentInfo1 = new Index\SegmentInfo($directory, '_3', 2);
        $this->assertTrue($segmentInfo1->isDeleted(0));
        $this->assertFalse($segmentInfo1->isDeleted(1));
    }

    public function testTermStreamStyleReading()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_3', 2);

        $this->assertEquals($segmentInfo->resetTermsStream(6, Index\SegmentInfo::SM_FULL_INFO), 8);

        $terms = array();

        $terms[] = $segmentInfo->currentTerm();
        $firstTermPositions = $segmentInfo->currentTermPositions();

        $this->assertEquals(count($firstTermPositions), 1);

        reset($firstTermPositions); // go to the first element
        $this->assertEquals(key($firstTermPositions), 7);

        $this->assertTrue(current($firstTermPositions) ==
                          array(105, 113, 130, 138, 153, 168, 171, 216, 243, 253, 258, 265, 302, 324,
                                331, 351, 359, 366, 370, 376, 402, 410, 418, 425, 433, 441, 460, 467));

        while (($term = $segmentInfo->nextTerm()) != null) {
            $terms[] = $term;
        }

        $this->assertTrue($terms ==
                          array(new Index\Term('a', 'contents'),
                                new Index\Term('about', 'contents'),
                                new Index\Term('accesskey', 'contents'),
                                new Index\Term('align', 'contents'),
                                new Index\Term('alink', 'contents'),
                                new Index\Term('already', 'contents'),
                                new Index\Term('and', 'contents'),
                                new Index\Term('are', 'contents'),
                                new Index\Term('at', 'contents'),
                                new Index\Term('b', 'contents'),
                                new Index\Term('be', 'contents'),
                                new Index\Term('been', 'contents'),
                                new Index\Term('bgcolor', 'contents'),
                                new Index\Term('body', 'contents'),
                                new Index\Term('border', 'contents'),
                                new Index\Term('bottom', 'contents'),
                                new Index\Term('bug', 'contents'),
                                new Index\Term('bugs', 'contents'),
                                new Index\Term('can', 'contents'),
                                new Index\Term('care', 'contents'),
                                new Index\Term('cellpadding', 'contents'),
                                new Index\Term('cellspacing', 'contents'),
                                new Index\Term('center', 'contents'),
                                new Index\Term('chapter', 'contents'),
                                new Index\Term('charset', 'contents'),
                                new Index\Term('check', 'contents'),
                                new Index\Term('class', 'contents'),
                                new Index\Term('click', 'contents'),
                                new Index\Term('colspan', 'contents'),
                                new Index\Term('contains', 'contents'),
                                new Index\Term('content', 'contents'),
                                new Index\Term('contributing', 'contents'),
                                new Index\Term('developers', 'contents'),
                                new Index\Term('div', 'contents'),
                                new Index\Term('docbook', 'contents'),
                                new Index\Term('documentation', 'contents'),
                                new Index\Term('does', 'contents'),
                                new Index\Term('don', 'contents'),
                                new Index\Term('double', 'contents'),
                                new Index\Term('easiest', 'contents'),
                                new Index\Term('equiv', 'contents'),
                                new Index\Term('existing', 'contents'),
                                new Index\Term('explanations', 'contents'),
                                new Index\Term('ff', 'contents'),
                                new Index\Term('ffffff', 'contents'),
                                new Index\Term('fill', 'contents'),
                                new Index\Term('find', 'contents'),
                                new Index\Term('fixed', 'contents'),
                                new Index\Term('footer', 'contents'),
                                new Index\Term('for', 'contents'),
                                new Index\Term('form', 'contents'),
                                new Index\Term('found', 'contents'),
                                new Index\Term('generator', 'contents'),
                                new Index\Term('guide', 'contents'),
                                new Index\Term('h', 'contents'),
                                new Index\Term('hasn', 'contents'),
                                new Index\Term('have', 'contents'),
                                new Index\Term('head', 'contents'),
                                new Index\Term('header', 'contents'),
                                new Index\Term('hesitate', 'contents'),
                                new Index\Term('home', 'contents'),
                                new Index\Term('homepage', 'contents'),
                                new Index\Term('how', 'contents'),
                                new Index\Term('hr', 'contents'),
                                new Index\Term('href', 'contents'),
                                new Index\Term('html', 'contents'),
                                new Index\Term('http', 'contents'),
                                new Index\Term('if', 'contents'),
                                new Index\Term('in', 'contents'),
                                new Index\Term('index', 'contents'),
                                new Index\Term('information', 'contents'),
                                new Index\Term('is', 'contents'),
                                new Index\Term('iso', 'contents'),
                                new Index\Term('it', 'contents'),
                                new Index\Term('latest', 'contents'),
                                new Index\Term('left', 'contents'),
                                new Index\Term('link', 'contents'),
                                new Index\Term('list', 'contents'),
                                new Index\Term('manual', 'contents'),
                                new Index\Term('meet', 'contents'),
                                new Index\Term('meta', 'contents'),
                                new Index\Term('modular', 'contents'),
                                new Index\Term('more', 'contents'),
                                new Index\Term('n', 'contents'),
                                new Index\Term('name', 'contents'),
                                new Index\Term('navfooter', 'contents'),
                                new Index\Term('navheader', 'contents'),
                                new Index\Term('navigation', 'contents'),
                                new Index\Term('net', 'contents'),
                                new Index\Term('new', 'contents'),
                                new Index\Term('newpackage', 'contents'),
                                new Index\Term('next', 'contents'),
                                new Index\Term('of', 'contents'),
                                new Index\Term('on', 'contents'),
                                new Index\Term('out', 'contents'),
                                new Index\Term('p', 'contents'),
                                new Index\Term('package', 'contents'),
                                new Index\Term('packages', 'contents'),
                                new Index\Term('page', 'contents'),
                                new Index\Term('patches', 'contents'),
                                new Index\Term('pear', 'contents'),
                                new Index\Term('persists', 'contents'),
                                new Index\Term('php', 'contents'),
                                new Index\Term('please', 'contents'),
                                new Index\Term('prev', 'contents'),
                                new Index\Term('previous', 'contents'),
                                new Index\Term('proper', 'contents'),
                                new Index\Term('quote', 'contents'),
                                new Index\Term('read', 'contents'),
                                new Index\Term('rel', 'contents'),
                                new Index\Term('report', 'contents'),
                                new Index\Term('reported', 'contents'),
                                new Index\Term('reporting', 'contents'),
                                new Index\Term('requirements', 'contents'),
                                new Index\Term('right', 'contents'),
                                new Index\Term('sect', 'contents'),
                                new Index\Term('span', 'contents'),
                                new Index\Term('still', 'contents'),
                                new Index\Term('stylesheet', 'contents'),
                                new Index\Term('submitting', 'contents'),
                                new Index\Term('summary', 'contents'),
                                new Index\Term('system', 'contents'),
                                new Index\Term('t', 'contents'),
                                new Index\Term('table', 'contents'),
                                new Index\Term('take', 'contents'),
                                new Index\Term('target', 'contents'),
                                new Index\Term('td', 'contents'),
                                new Index\Term('text', 'contents'),
                                new Index\Term('th', 'contents'),
                                new Index\Term('that', 'contents'),
                                new Index\Term('the', 'contents'),
                                new Index\Term('think', 'contents'),
                                new Index\Term('this', 'contents'),
                                new Index\Term('tips', 'contents'),
                                new Index\Term('title', 'contents'),
                                new Index\Term('to', 'contents'),
                                new Index\Term('top', 'contents'),
                                new Index\Term('tr', 'contents'),
                                new Index\Term('translating', 'contents'),
                                new Index\Term('type', 'contents'),
                                new Index\Term('u', 'contents'),
                                new Index\Term('unable', 'contents'),
                                new Index\Term('up', 'contents'),
                                new Index\Term('using', 'contents'),
                                new Index\Term('valign', 'contents'),
                                new Index\Term('version', 'contents'),
                                new Index\Term('vlink', 'contents'),
                                new Index\Term('way', 'contents'),
                                new Index\Term('which', 'contents'),
                                new Index\Term('width', 'contents'),
                                new Index\Term('will', 'contents'),
                                new Index\Term('with', 'contents'),
                                new Index\Term('writing', 'contents'),
                                new Index\Term('you', 'contents'),
                                new Index\Term('your', 'contents'),
                                new Index\Term('1178009946', 'modified'),
                                new Index\Term('bugs', 'path'),
                                new Index\Term('contributing', 'path'),
                                new Index\Term('html', 'path'),
                                new Index\Term('indexsource', 'path'),
                                new Index\Term('newpackage', 'path'),
                               ));

        unset($segmentInfo);


        $segmentInfo1 = new Index\SegmentInfo($directory, '_3', 2);
        $this->assertEquals($segmentInfo1->resetTermsStream(6, Index\SegmentInfo::SM_MERGE_INFO), 7);
    }

    public function testTermStreamStyleReadingSkipTo()
    {
        $directory = new Directory\Filesystem(__DIR__ . '/_source/_files');

        $segmentInfo = new Index\SegmentInfo($directory, '_3', 2);

        $this->assertEquals($segmentInfo->resetTermsStream(6, Index\SegmentInfo::SM_FULL_INFO), 8);

        $segmentInfo->skipTo(new Index\Term('prefetch', 'contents'));

        $terms = array();

        $terms[] = $segmentInfo->currentTerm();
        $firstTermPositions = $segmentInfo->currentTermPositions();

        $this->assertEquals(count($firstTermPositions), 1);

        reset($firstTermPositions); // go to the first element
        $this->assertEquals(key($firstTermPositions), 7);
        $this->assertTrue(current($firstTermPositions) == array(112, 409));

        while (($term = $segmentInfo->nextTerm()) != null) {
            $terms[] = $term;
        }

        $this->assertTrue($terms ==
                          array(new Index\Term('prev', 'contents'),
                                new Index\Term('previous', 'contents'),
                                new Index\Term('proper', 'contents'),
                                new Index\Term('quote', 'contents'),
                                new Index\Term('read', 'contents'),
                                new Index\Term('rel', 'contents'),
                                new Index\Term('report', 'contents'),
                                new Index\Term('reported', 'contents'),
                                new Index\Term('reporting', 'contents'),
                                new Index\Term('requirements', 'contents'),
                                new Index\Term('right', 'contents'),
                                new Index\Term('sect', 'contents'),
                                new Index\Term('span', 'contents'),
                                new Index\Term('still', 'contents'),
                                new Index\Term('stylesheet', 'contents'),
                                new Index\Term('submitting', 'contents'),
                                new Index\Term('summary', 'contents'),
                                new Index\Term('system', 'contents'),
                                new Index\Term('t', 'contents'),
                                new Index\Term('table', 'contents'),
                                new Index\Term('take', 'contents'),
                                new Index\Term('target', 'contents'),
                                new Index\Term('td', 'contents'),
                                new Index\Term('text', 'contents'),
                                new Index\Term('th', 'contents'),
                                new Index\Term('that', 'contents'),
                                new Index\Term('the', 'contents'),
                                new Index\Term('think', 'contents'),
                                new Index\Term('this', 'contents'),
                                new Index\Term('tips', 'contents'),
                                new Index\Term('title', 'contents'),
                                new Index\Term('to', 'contents'),
                                new Index\Term('top', 'contents'),
                                new Index\Term('tr', 'contents'),
                                new Index\Term('translating', 'contents'),
                                new Index\Term('type', 'contents'),
                                new Index\Term('u', 'contents'),
                                new Index\Term('unable', 'contents'),
                                new Index\Term('up', 'contents'),
                                new Index\Term('using', 'contents'),
                                new Index\Term('valign', 'contents'),
                                new Index\Term('version', 'contents'),
                                new Index\Term('vlink', 'contents'),
                                new Index\Term('way', 'contents'),
                                new Index\Term('which', 'contents'),
                                new Index\Term('width', 'contents'),
                                new Index\Term('will', 'contents'),
                                new Index\Term('with', 'contents'),
                                new Index\Term('writing', 'contents'),
                                new Index\Term('you', 'contents'),
                                new Index\Term('your', 'contents'),
                                new Index\Term('1178009946', 'modified'),
                                new Index\Term('bugs', 'path'),
                                new Index\Term('contributing', 'path'),
                                new Index\Term('html', 'path'),
                                new Index\Term('indexsource', 'path'),
                                new Index\Term('newpackage', 'path'),
                               ));

        unset($segmentInfo);


        $segmentInfo1 = new Index\SegmentInfo($directory, '_3', 2);
        $this->assertEquals($segmentInfo1->resetTermsStream(6, Index\SegmentInfo::SM_MERGE_INFO), 7);
    }
}

