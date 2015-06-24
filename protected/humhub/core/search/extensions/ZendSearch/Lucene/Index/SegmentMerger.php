<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Index;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Storage\Directory;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class SegmentMerger
{
    /**
     * Target segment writer
     *
     * @var \ZendSearch\Lucene\Index\SegmentWriter\StreamWriter
     */
    private $_writer;

    /**
     * Number of docs in a new segment
     *
     * @var integer
     */
    private $_docCount;

    /**
     * A set of segments to be merged
     *
     * @var array|\ZendSearch\Lucene\Index\SegmentInfo
     */
    private $_segmentInfos = array();

    /**
     * Flag to signal, that merge is already done
     *
     * @var boolean
     */
    private $_mergeDone = false;

    /**
     * Field map
     * [<segment_name>][<field_number>] => <target_field_number>
     *
     * @var array
     */
    private $_fieldsMap = array();



    /**
     * Object constructor.
     *
     * Creates new segment merger with $directory as target to merge segments into
     * and $name as a name of new segment
     *
     * @param \ZendSearch\Lucene\Storage\Directory\DirectoryInterface $directory
     * @param string $name
     */
    public function __construct(Directory\DirectoryInterface $directory, $name)
    {
        /** \ZendSearch\Lucene\Index\SegmentWriter\StreamWriter */
        $this->_writer = new SegmentWriter\StreamWriter($directory, $name);
    }


    /**
     * Add segmnet to a collection of segments to be merged
     *
     * @param \ZendSearch\Lucene\Index\SegmentInfo $segment
     */
    public function addSource(SegmentInfo $segmentInfo)
    {
        $this->_segmentInfos[$segmentInfo->getName()] = $segmentInfo;
    }


    /**
     * Do merge.
     *
     * Returns number of documents in newly created segment
     *
     * @return \ZendSearch\Lucene\Index\SegmentInfo
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    public function merge()
    {
        if ($this->_mergeDone) {
            throw new RuntimeException('Merge is already done.');
        }

        if (count($this->_segmentInfos) < 1) {
            throw new RuntimeException('Wrong number of segments to be merged ('
                                                 . count($this->_segmentInfos)
                                                 . ').');
        }

        $this->_mergeFields();
        $this->_mergeNorms();
        $this->_mergeStoredFields();
        $this->_mergeTerms();

        $this->_mergeDone = true;

        return $this->_writer->close();
    }


    /**
     * Merge fields information
     */
    private function _mergeFields()
    {
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            foreach ($segmentInfo->getFieldInfos() as $fieldInfo) {
                $this->_fieldsMap[$segName][$fieldInfo->number] = $this->_writer->addFieldInfo($fieldInfo);
            }
        }
    }

    /**
     * Merge field's normalization factors
     */
    private function _mergeNorms()
    {
        foreach ($this->_writer->getFieldInfos() as $fieldInfo) {
            if ($fieldInfo->isIndexed) {
                foreach ($this->_segmentInfos as $segName => $segmentInfo) {
                    if ($segmentInfo->hasDeletions()) {
                        $srcNorm = $segmentInfo->normVector($fieldInfo->name);
                        $norm    = '';
                        $docs    = $segmentInfo->count();
                        for ($count = 0; $count < $docs; $count++) {
                            if (!$segmentInfo->isDeleted($count)) {
                                $norm .= $srcNorm[$count];
                            }
                        }
                        $this->_writer->addNorm($fieldInfo->name, $norm);
                    } else {
                        $this->_writer->addNorm($fieldInfo->name, $segmentInfo->normVector($fieldInfo->name));
                    }
                }
            }
        }
    }

    /**
     * Merge fields information
     */
    private function _mergeStoredFields()
    {
        $this->_docCount = 0;

        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $fdtFile = $segmentInfo->openCompoundFile('.fdt');

            for ($count = 0; $count < $segmentInfo->count(); $count++) {
                $fieldCount = $fdtFile->readVInt();
                $storedFields = array();

                for ($count2 = 0; $count2 < $fieldCount; $count2++) {
                    $fieldNum = $fdtFile->readVInt();
                    $bits = $fdtFile->readByte();
                    $fieldInfo = $segmentInfo->getField($fieldNum);

                    if (!($bits & 2)) { // Text data
                        $storedFields[] =
                                 new Document\Field($fieldInfo->name,
                                                    $fdtFile->readString(),
                                                    'UTF-8',
                                                    true,
                                                    $fieldInfo->isIndexed,
                                                    $bits & 1 );
                    } else {            // Binary data
                        $storedFields[] =
                                 new Document\Field($fieldInfo->name,
                                                    $fdtFile->readBinary(),
                                                    '',
                                                    true,
                                                    $fieldInfo->isIndexed,
                                                    $bits & 1,
                                                    true);
                    }
                }

                if (!$segmentInfo->isDeleted($count)) {
                    $this->_docCount++;
                    $this->_writer->addStoredFields($storedFields);
                }
            }
        }
    }


    /**
     * Merge fields information
     */
    private function _mergeTerms()
    {
        $segmentInfoQueue = new TermsPriorityQueue();

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $segmentStartId = $segmentInfo->resetTermsStream($segmentStartId, SegmentInfo::SM_MERGE_INFO);

            // Skip "empty" segments
            if ($segmentInfo->currentTerm() !== null) {
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        $this->_writer->initializeDictionaryFiles();

        $termDocs = array();
        while (($segmentInfo = $segmentInfoQueue->pop()) !== null) {
            // Merge positions array
            $termDocs += $segmentInfo->currentTermPositions();

            if ($segmentInfoQueue->top() === null ||
                $segmentInfoQueue->top()->currentTerm()->key() !=
                            $segmentInfo->currentTerm()->key()) {
                // We got new term
                ksort($termDocs, SORT_NUMERIC);

                // Add term if it's contained in any document
                if (count($termDocs) > 0) {
                    $this->_writer->addTerm($segmentInfo->currentTerm(), $termDocs);
                }
                $termDocs = array();
            }

            $segmentInfo->nextTerm();
            // check, if segment dictionary is finished
            if ($segmentInfo->currentTerm() !== null) {
                // Put segment back into the priority queue
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        $this->_writer->closeDictionaryFiles();
    }
}
