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

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class DictionaryLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $directory = new \ZendSearch\Lucene\Storage\Directory\Filesystem(__DIR__ . '/_source/_files');

        $stiFile = $directory->getFileObject('_1.sti');
        $stiFileData = $stiFile->readBytes($directory->fileLength('_1.sti'));

        // Load dictionary index data
        list($termDictionary, $termDictionaryInfos) = unserialize($stiFileData);


        $segmentInfo = new \ZendSearch\Lucene\Index\SegmentInfo($directory, '_1', 2);
        $tiiFile = $segmentInfo->openCompoundFile('.tii');
        $tiiFileData = $tiiFile->readBytes($segmentInfo->compoundFileLength('.tii'));

        // Load dictionary index data
        list($loadedTermDictionary, $loadedTermDictionaryInfos) =
                    \ZendSearch\Lucene\Index\DictionaryLoader::load($tiiFileData);

        $this->assertTrue($termDictionary == $loadedTermDictionary);
        $this->assertTrue($termDictionaryInfos == $loadedTermDictionaryInfos);
    }
}

