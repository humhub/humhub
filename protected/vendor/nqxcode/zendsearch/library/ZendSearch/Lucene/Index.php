<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene;

use ZendSearch\Lucene\Exception\InvalidArgumentException;
use ZendSearch\Lucene\Exception\InvalidFileFormatException;
use ZendSearch\Lucene\Exception\OutOfRangeException;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Search\Similarity\AbstractSimilarity;
use ZendSearch\Lucene\Storage\Directory;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 */
class Index implements SearchIndexInterface
{
    /**
     * File system adapter.
     *
     * @var Directory\DirectoryInterface
     */
    private $_directory = null;

    /**
     * File system adapter closing option
     *
     * @var boolean
     */
    private $_closeDirOnExit = true;

    /**
     * Writer for this index, not instantiated unless required.
     *
     * @var \ZendSearch\Lucene\Index\Writer
     */
    private $_writer = null;

    /**
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects for current version of index.
     *
     * @var array|\ZendSearch\Lucene\Index\SegmentInfo
     */
    private $_segmentInfos = array();

    /**
     * Number of documents in this index.
     *
     * @var integer
     */
    private $_docCount = 0;

    /**
     * Flag for index changes
     *
     * @var boolean
     */
    private $_hasChanges = false;

    /**
     * Current segment generation
     *
     * @var integer
     */
    private $_generation;

    const FORMAT_PRE_2_1 = 0;
    const FORMAT_2_1     = 1;
    const FORMAT_2_3     = 2;


    /**
     * Index format version
     *
     * @var integer
     */
    private $_formatVersion;

    /** Generation retrieving counter */
    const GENERATION_RETRIEVE_COUNT = 10;

    /** Pause between generation retrieving attempts in milliseconds */
    const GENERATION_RETRIEVE_PAUSE = 50;

    /**
     * Get current generation number
     *
     * Returns generation number
     * 0 means pre-2.1 index format
     * -1 means there are no segments files.
     *
     * @param \ZendSearch\Lucene\Storage\Directory\DirectoryInterface $directory
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     * @return integer
     */
    public static function getActualGeneration(Directory\DirectoryInterface $directory)
    {
        /**
         * Zend_Search_Lucene uses segments.gen file to retrieve current generation number
         *
         * Apache Lucene index format documentation mentions this method only as a fallback method
         *
         * Nevertheless we use it according to the performance considerations
         *
         * @todo check if we can use some modification of Apache Lucene generation determination algorithm
         *       without performance problems
         */
        try {
            for ($count = 0; $count < self::GENERATION_RETRIEVE_COUNT; $count++) {
                // Try to get generation file
                $genFile = $directory->getFileObject('segments.gen', false);

                $format = $genFile->readInt();
                if ($format != (int)0xFFFFFFFE) {
                    throw new RuntimeException('Wrong segments.gen file format');
                }

                $gen1 = $genFile->readLong();
                $gen2 = $genFile->readLong();

                if ($gen1 == $gen2) {
                    return $gen1;
                }

                usleep(self::GENERATION_RETRIEVE_PAUSE * 1000);
            }

            // All passes are failed
            throw new RuntimeException('Index is under processing now');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'is not readable') !== false) {
                try {
                    // Try to open old style segments file
                    $segmentsFile = $directory->getFileObject('segments', false);

                    // It's pre-2.1 index
                    return 0;
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'is not readable') !== false) {
                        return -1;
                    } else {
                        throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            } else {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return -1;
    }

    /**
     * Get generation number associated with this index instance
     *
     * The same generation number in pair with document number or query string
     * guarantees to give the same result while index retrieving.
     * So it may be used for search result caching.
     *
     * @return integer
     */
    public function getGeneration()
    {
        return $this->_generation;
    }


    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public static function getSegmentFileName($generation)
    {
        if ($generation == 0) {
            return 'segments';
        }

        return 'segments_' . base_convert($generation, 10, 36);
    }

    /**
     * Get index format version
     *
     * @return integer
     */
    public function getFormatVersion()
    {
        return $this->_formatVersion;
    }

    /**
     * Set index format version.
     * Index is converted to this format at the nearest upfdate time
     *
     * @param int $formatVersion
     * @throws \ZendSearch\Lucene\Exception\InvalidArgumentException
     */
    public function setFormatVersion($formatVersion)
    {
        if ($formatVersion != self::FORMAT_PRE_2_1  &&
            $formatVersion != self::FORMAT_2_1  &&
            $formatVersion != self::FORMAT_2_3) {
            throw new InvalidArgumentException('Unsupported index format');
        }

        $this->_formatVersion = $formatVersion;
    }

    /**
     * Read segments file for pre-2.1 Lucene index format
     *
     * @throws \ZendSearch\Lucene\Exception\InvalidFileFormatException
     */
    private function _readPre21SegmentsFile()
    {
        $segmentsFile = $this->_directory->getFileObject('segments');

        $format = $segmentsFile->readInt();

        if ($format != (int)0xFFFFFFFF) {
            throw new InvalidFileFormatException('Wrong segments file format');
        }

        // read version
        $segmentsFile->readLong();

        // read segment name counter
        $segmentsFile->readInt();

        $segments = $segmentsFile->readInt();

        $this->_docCount = 0;

        // read segmentInfos
        for ($count = 0; $count < $segments; $count++) {
            $segName = $segmentsFile->readString();
            $segSize = $segmentsFile->readInt();
            $this->_docCount += $segSize;

            $this->_segmentInfos[$segName] = new Index\SegmentInfo($this->_directory,
                                                                   $segName,
                                                                   $segSize);
        }

        // Use 2.1 as a target version. Index will be reorganized at update time.
        $this->_formatVersion = self::FORMAT_2_1;
    }

    /**
     * Read segments file
     *
     * @throws \ZendSearch\Lucene\Exception\InvalidFileFormatException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    private function _readSegmentsFile()
    {
        $segmentsFile = $this->_directory->getFileObject(self::getSegmentFileName($this->_generation));

        $format = $segmentsFile->readInt();

        if ($format == (int)0xFFFFFFFC) {
            $this->_formatVersion = self::FORMAT_2_3;
        } elseif ($format == (int)0xFFFFFFFD) {
            $this->_formatVersion = self::FORMAT_2_1;
        } else {
            throw new InvalidFileFormatException('Unsupported segments file format');
        }

        // read version
        $segmentsFile->readLong();

        // read segment name counter
        $segmentsFile->readInt();

        $segments = $segmentsFile->readInt();

        $this->_docCount = 0;

        // read segmentInfos
        for ($count = 0; $count < $segments; $count++) {
            $segName = $segmentsFile->readString();
            $segSize = $segmentsFile->readInt();

            // 2.1+ specific properties
            $delGen = $segmentsFile->readLong();

            if ($this->_formatVersion == self::FORMAT_2_3) {
                $docStoreOffset = $segmentsFile->readInt();

                if ($docStoreOffset != (int)0xFFFFFFFF) {
                    $docStoreSegment        = $segmentsFile->readString();
                    $docStoreIsCompoundFile = $segmentsFile->readByte();

                    $docStoreOptions = array('offset'     => $docStoreOffset,
                                             'segment'    => $docStoreSegment,
                                             'isCompound' => ($docStoreIsCompoundFile == 1));
                } else {
                    $docStoreOptions = null;
                }
            } else {
                $docStoreOptions = null;
            }

            $hasSingleNormFile = $segmentsFile->readByte();
            $numField          = $segmentsFile->readInt();

            $normGens = array();
            if ($numField != (int)0xFFFFFFFF) {
                for ($count1 = 0; $count1 < $numField; $count1++) {
                    $normGens[] = $segmentsFile->readLong();
                }

                throw new RuntimeException(
                    'Separate norm files are not supported. Optimize index to use it with ZendSearch\Lucene.'
                );
            }

            $isCompoundByte     = $segmentsFile->readByte();

            if ($isCompoundByte == 0xFF) {
                // The segment is not a compound file
                $isCompound = false;
            } elseif ($isCompoundByte == 0x00) {
                // The status is unknown
                $isCompound = null;
            } elseif ($isCompoundByte == 0x01) {
                // The segment is a compound file
                $isCompound = true;
            }

            $this->_docCount += $segSize;

            $this->_segmentInfos[$segName] = new Index\SegmentInfo($this->_directory,
                                                                   $segName,
                                                                   $segSize,
                                                                   $delGen,
                                                                   $docStoreOptions,
                                                                   $hasSingleNormFile,
                                                                   $isCompound);
        }
    }

    /**
     * Opens the index.
     *
     * IndexReader constructor needs Directory as a parameter. It should be
     * a string with a path to the index folder or a Directory object.
     *
     * @param \ZendSearch\Lucene\Storage\Directory\Filesystem|string $directory
     * @throws \ZendSearch\Lucene\Exception\InvalidArgumentException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    public function __construct($directory = null, $create = false)
    {
        if ($directory === null) {
            throw new InvalidArgumentException('No index directory specified');
        }

        if (is_string($directory)) {
            $this->_directory      = new Directory\Filesystem($directory);
            $this->_closeDirOnExit = true;
        } else {
            $this->_directory      = $directory;
            $this->_closeDirOnExit = false;
        }

        $this->_segmentInfos = array();

        // Mark index as "under processing" to prevent other processes from premature index cleaning
        LockManager::obtainReadLock($this->_directory);

        $this->_generation = self::getActualGeneration($this->_directory);

        if ($create) {
            try {
                LockManager::obtainWriteLock($this->_directory);
            } catch (\Exception $e) {
                LockManager::releaseReadLock($this->_directory);

                if (strpos($e->getMessage(), 'Can\'t obtain exclusive index lock') === false) {
                    throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
                } else {
                    throw new RuntimeException('Can\'t create index. It\'s under processing now', 0, $e);
                }
            }

            if ($this->_generation == -1) {
                // Directory doesn't contain existing index, start from 1
                $this->_generation = 1;
                $nameCounter = 0;
            } else {
                // Directory contains existing index
                $segmentsFile = $this->_directory->getFileObject(self::getSegmentFileName($this->_generation));
                $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)

                $nameCounter = $segmentsFile->readInt();
                $this->_generation++;
            }

            Index\Writer::createIndex($this->_directory, $this->_generation, $nameCounter);

            LockManager::releaseWriteLock($this->_directory);
        }

        if ($this->_generation == -1) {
            throw new RuntimeException('Index doesn\'t exists in the specified directory.');
        } elseif ($this->_generation == 0) {
            $this->_readPre21SegmentsFile();
        } else {
            $this->_readSegmentsFile();
        }
    }

    /**
     * Object destructor
     */
    public function __destruct()
    {
        $this->commit();

        // Release "under processing" flag
        LockManager::releaseReadLock($this->_directory);

        if ($this->_closeDirOnExit) {
            $this->_directory->close();
        }

        $this->_directory    = null;
        $this->_writer       = null;
        $this->_segmentInfos = null;
    }

    /**
     * Returns an instance of Zend_Search_Lucene_Index_Writer for the index
     *
     * @return \ZendSearch\Lucene\Index\Writer
     */
    private function _getIndexWriter()
    {
        if ($this->_writer === null) {
            $this->_writer = new Index\Writer($this->_directory,
                                              $this->_segmentInfos,
                                              $this->_formatVersion);
        }

        return $this->_writer;
    }


    /**
     * Returns the Zend_Search_Lucene_Storage_Directory instance for this index.
     *
     * @return \ZendSearch\Lucene\Storage\Directory\DirectoryInterface
     */
    public function getDirectory()
    {
        return $this->_directory;
    }


    /**
     * Returns the total number of documents in this index (including deleted documents).
     *
     * @return integer
     */
    public function count()
    {
        return $this->_docCount;
    }

    /**
     * Returns one greater than the largest possible document number.
     * This may be used to, e.g., determine how big to allocate a structure which will have
     * an element for every document number in an index.
     *
     * @return integer
     */
    public function maxDoc()
    {
        return $this->count();
    }

    /**
     * Returns the total number of non-deleted documents in this index.
     *
     * @return integer
     */
    public function numDocs()
    {
        $numDocs = 0;

        foreach ($this->_segmentInfos as $segmentInfo) {
            $numDocs += $segmentInfo->numDocs();
        }

        return $numDocs;
    }

    /**
     * Checks, that document is deleted
     *
     * @param integer $id
     * @return boolean
     * @throws \ZendSearch\Lucene\Exception\OutOfRangeException	is thrown if $id is out of the range
     */
    public function isDeleted($id)
    {
        if ($id >= $this->_docCount) {
            throw new OutOfRangeException('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }

        if (isset($segmentInfo)) {
            return $segmentInfo->isDeleted($id - $segmentStartId);
        }
        return false;
    }

    /**
     * Retrieve index maxBufferedDocs option
     *
     * maxBufferedDocs is a minimal number of documents required before
     * the buffered in-memory documents are written into a new Segment
     *
     * Default value is 10
     *
     * @return integer
     */
    public function getMaxBufferedDocs()
    {
        return $this->_getIndexWriter()->maxBufferedDocs;
    }

    /**
     * Set index maxBufferedDocs option
     *
     * maxBufferedDocs is a minimal number of documents required before
     * the buffered in-memory documents are written into a new Segment
     *
     * Default value is 10
     *
     * @param integer $maxBufferedDocs
     */
    public function setMaxBufferedDocs($maxBufferedDocs)
    {
        $this->_getIndexWriter()->maxBufferedDocs = $maxBufferedDocs;
    }

    /**
     * Retrieve index maxMergeDocs option
     *
     * maxMergeDocs is a largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @return integer
     */
    public function getMaxMergeDocs()
    {
        return $this->_getIndexWriter()->maxMergeDocs;
    }

    /**
     * Set index maxMergeDocs option
     *
     * maxMergeDocs is a largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @param integer $maxMergeDocs
     */
    public function setMaxMergeDocs($maxMergeDocs)
    {
        $this->_getIndexWriter()->maxMergeDocs = $maxMergeDocs;
    }

    /**
     * Retrieve index mergeFactor option
     *
     * mergeFactor determines how often segment indices are merged by addDocument().
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @return integer
     */
    public function getMergeFactor()
    {
        return $this->_getIndexWriter()->mergeFactor;
    }

    /**
     * Set index mergeFactor option
     *
     * mergeFactor determines how often segment indices are merged by addDocument().
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @param integer $maxMergeDocs
     */
    public function setMergeFactor($mergeFactor)
    {
        $this->_getIndexWriter()->mergeFactor = $mergeFactor;
    }

    /**
     * Performs a query against the index and returns an array
     * of Zend_Search_Lucene_Search_QueryHit objects.
     * Input is a string or Zend_Search_Lucene_Search_Query.
     *
     * @param \ZendSearch\Lucene\Search\QueryParser|string $query
     * @return array|\ZendSearch\Lucene\Search\QueryHit
     * @throws \ZendSearch\Lucene\Exception\InvalidArgumentException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    public function find($query)
    {
        if (is_string($query)) {
            $query = Search\QueryParser::parse($query);
        } elseif (!$query instanceof Search\Query\AbstractQuery) {
            throw new InvalidArgumentException('Query must be a string or ZendSearch\Lucene\Search\Query object');
        }

        $this->commit();

        $hits   = array();
        $scores = array();
        $ids    = array();

        $query = $query->rewrite($this)->optimize($this);

        $query->execute($this);

        $topScore = 0;

        $resultSetLimit = Lucene::getResultSetLimit();
        foreach ($query->matchedDocs() as $id => $num) {
            $docScore = $query->score($id, $this);
            if( $docScore != 0 ) {
                $hit = new Search\QueryHit($this);
                $hit->document_id = $hit->id = $id;
                $hit->score = $docScore;

                $hits[]   = $hit;
                $ids[]    = $id;
                $scores[] = $docScore;

                if ($docScore > $topScore) {
                    $topScore = $docScore;
                }
            }

            if ($resultSetLimit != 0  &&  count($hits) >= $resultSetLimit) {
                break;
            }
        }

        if (count($hits) == 0) {
            // skip sorting, which may cause a error on empty index
            return array();
        }

        if ($topScore > 1) {
            foreach ($hits as $hit) {
                $hit->score /= $topScore;
            }
        }

        if (func_num_args() == 1) {
            // sort by scores
            array_multisort($scores, SORT_DESC, SORT_NUMERIC,
                            $ids,    SORT_ASC,  SORT_NUMERIC,
                            $hits);
        } else {
            // sort by given field names

            $argList    = func_get_args();
            $fieldNames = $this->getFieldNames();
            $sortArgs   = array();

            // PHP 5.3 now expects all arguments to array_multisort be passed by
            // reference (if it's invoked through call_user_func_array());
            // since constants can't be passed by reference, create some placeholder variables.
            $sortReg    = SORT_REGULAR;
            $sortAsc    = SORT_ASC;
            $sortNum    = SORT_NUMERIC;

            $sortFieldValues = array();

            for ($count = 1; $count < count($argList); $count++) {
                $fieldName = $argList[$count];

                if (!is_string($fieldName)) {
                    throw new RuntimeException('Field name must be a string.');
                }

                if (strtolower($fieldName) == 'score') {
                    $sortArgs[] = &$scores;
                } else {
                    if (!in_array($fieldName, $fieldNames)) {
                        throw new RuntimeException('Wrong field name.');
                    }

                    if (!isset($sortFieldValues[$fieldName])) {
                        $valuesArray = array();
                        foreach ($hits as $hit) {
                            try {
                                $value = $hit->getDocument()->getFieldValue($fieldName);
                            } catch (\Exception $e) {
                                if (strpos($e->getMessage(), 'not found') === false) {
                                    throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
                                } else {
                                    $value = null;
                                }
                            }

                            $valuesArray[] = $value;
                        }

                        // Collect loaded values in $sortFieldValues
                        // Required for PHP 5.3 which translates references into values when source
                        // variable is destroyed
                        $sortFieldValues[$fieldName] = $valuesArray;
                    }

                    $sortArgs[] = &$sortFieldValues[$fieldName];
                }

                if ($count + 1 < count($argList)  &&  is_integer($argList[$count+1])) {
                    $count++;
                    $sortArgs[] = &$argList[$count];

                    if ($count + 1 < count($argList)  &&  is_integer($argList[$count+1])) {
                        $count++;
                        $sortArgs[] = &$argList[$count];
                    } else {
                        if ($argList[$count] == SORT_ASC  || $argList[$count] == SORT_DESC) {
                            $sortArgs[] = &$sortReg;
                        } else {
                            $sortArgs[] = &$sortAsc;
                        }
                    }
                } else {
                    $sortArgs[] = &$sortAsc;
                    $sortArgs[] = &$sortReg;
                }
            }

            // Sort by id's if values are equal
            $sortArgs[] = &$ids;
            $sortArgs[] = &$sortAsc;
            $sortArgs[] = &$sortNum;

            // Array to be sorted
            $sortArgs[] = &$hits;

            // Do sort
            call_user_func_array('array_multisort', $sortArgs);
        }

        return $hits;
    }


    /**
     * Returns a list of all unique field names that exist in this index.
     *
     * @param boolean $indexed
     * @return array
     */
    public function getFieldNames($indexed = false)
    {
        $result = array();
        foreach( $this->_segmentInfos as $segmentInfo ) {
            $result = array_merge($result, $segmentInfo->getFields($indexed));
        }
        return $result;
    }


    /**
     * Returns a Zend_Search_Lucene_Document object for the document
     * number $id in this index.
     *
     * @param integer|\ZendSearch\Lucene\Search\QueryHit $id
     * @return \ZendSearch\Lucene\Document
     * @throws \ZendSearch\Lucene\OutOfRangeException    is thrown if $id is out of the range
     */
    public function getDocument($id)
    {
        if ($id instanceof Search\QueryHit) {
            /* @var $id \ZendSearch\Lucene\Search\QueryHit */
            $id = $id->id;
        }

        if ($id >= $this->_docCount) {
            throw new OutOfRangeException('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }

        $fdxFile = $segmentInfo->openCompoundFile('.fdx');
        $fdxFile->seek(($id-$segmentStartId)*8, SEEK_CUR);
        $fieldValuesPosition = $fdxFile->readLong();

        $fdtFile = $segmentInfo->openCompoundFile('.fdt');
        $fdtFile->seek($fieldValuesPosition, SEEK_CUR);
        $fieldCount = $fdtFile->readVInt();

        $doc = new Document();
        for ($count = 0; $count < $fieldCount; $count++) {
            $fieldNum = $fdtFile->readVInt();
            $bits = $fdtFile->readByte();

            $fieldInfo = $segmentInfo->getField($fieldNum);

            if (!($bits & 2)) { // Text data
                $field = new Document\Field($fieldInfo->name,
                                            $fdtFile->readString(),
                                            'UTF-8',
                                            true,
                                            $fieldInfo->isIndexed,
                                            $bits & 1 );
            } else {            // Binary data
                $field = new Document\Field($fieldInfo->name,
                                            $fdtFile->readBinary(),
                                            '',
                                            true,
                                            $fieldInfo->isIndexed,
                                            $bits & 1,
                                            true );
            }

            $doc->addField($field);
        }

        return $doc;
    }


    /**
     * Returns true if index contain documents with specified term.
     *
     * Is used for query optimization.
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @return boolean
     */
    public function hasTerm(Index\Term $term)
    {
        foreach ($this->_segmentInfos as $segInfo) {
            if ($segInfo->getTermInfo($term) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns IDs of all documents containing term.
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     * @return array
     */
    public function termDocs(Index\Term $term, $docsFilter = null)
    {
        $subResults = array();
        $segmentStartDocId = 0;

        foreach ($this->_segmentInfos as $segmentInfo) {
            $subResults[] = $segmentInfo->termDocs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        if (count($subResults) == 0) {
            return array();
        } elseif (count($subResults) == 1) {
            // Index is optimized (only one segment)
            // Do not perform array reindexing
            return reset($subResults);
        } else {
            $result = call_user_func_array('array_merge', $subResults);
        }

        return $result;
    }

    /**
     * Returns documents filter for all documents containing term.
     *
     * It performs the same operation as termDocs, but return result as
     * Zend_Search_Lucene_Index_DocsFilter object
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     * @return \ZendSearch\Lucene\Index\DocsFilter
     */
    public function termDocsFilter(Index\Term $term, $docsFilter = null)
    {
        $segmentStartDocId = 0;
        $result = new Index\DocsFilter();

        foreach ($this->_segmentInfos as $segmentInfo) {
            $subResults[] = $segmentInfo->termDocs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        if (count($subResults) == 0) {
            return array();
        } elseif (count($subResults) == 1) {
            // Index is optimized (only one segment)
            // Do not perform array reindexing
            return reset($subResults);
        } else {
            $result = call_user_func_array('array_merge', $subResults);
        }

        return $result;
    }


    /**
     * Returns an array of all term freqs.
     * Result array structure: array(docId => freq, ...)
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     * @return integer
     */
    public function termFreqs(Index\Term $term, $docsFilter = null)
    {
        $result = array();
        $segmentStartDocId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            $result += $segmentInfo->termFreqs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        return $result;
    }

    /**
     * Returns an array of all term positions in the documents.
     * Result array structure: array(docId => array(pos1, pos2, ...), ...)
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     * @return array
     */
    public function termPositions(Index\Term $term, $docsFilter = null)
    {
        $result = array();
        $segmentStartDocId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            $result += $segmentInfo->termPositions($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        return $result;
    }


    /**
     * Returns the number of documents in this index containing the $term.
     *
     * @param \ZendSearch\Lucene\Index\Term $term
     * @return integer
     */
    public function docFreq(Index\Term $term)
    {
        $result = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            $termInfo = $segInfo->getTermInfo($term);
            if ($termInfo !== null) {
                $result += $termInfo->docFreq;
            }
        }

        return $result;
    }


    /**
     * Retrive similarity used by index reader
     *
     * @return \ZendSearch\Lucene\Search\Similarity\AbstractSimilarity
     */
    public function getSimilarity()
    {
        return AbstractSimilarity::getDefault();
    }


    /**
     * Returns a normalization factor for "field, document" pair.
     *
     * @param integer $id
     * @param string $fieldName
     * @return float
     */
    public function norm($id, $fieldName)
    {
        if ($id >= $this->_docCount) {
            return null;
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            if ($segmentStartId + $segInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segInfo->count();
        }

        if ($segInfo->isDeleted($id - $segmentStartId)) {
            return 0;
        }

        return $segInfo->norm($id - $segmentStartId, $fieldName);
    }

    /**
     * Returns true if any documents have been deleted from this index.
     *
     * @return boolean
     */
    public function hasDeletions()
    {
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentInfo->hasDeletions()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Deletes a document from the index.
     * $id is an internal document id
     *
     * @param integer|\ZendSearch\Lucene\Search\QueryHit $id
     * @throws \ZendSearch\Lucene\Exception\OutOfRangeException
     */
    public function delete($id)
    {
        if ($id instanceof Search\QueryHit) {
            /* @var $id \ZendSearch\Lucene\Search\QueryHit */
            $id = $id->id;
        }

        if ($id >= $this->_docCount) {
            throw new OutOfRangeException('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }
        $segmentInfo->delete($id - $segmentStartId);

        $this->_hasChanges = true;
    }



    /**
     * Adds a document to this index.
     *
     * @param \ZendSearch\Lucene\Document $document
     */
    public function addDocument(Document $document)
    {
        $this->_getIndexWriter()->addDocument($document);
        $this->_docCount++;

        $this->_hasChanges = true;
    }


    /**
     * Update document counter
     */
    private function _updateDocCount()
    {
        $this->_docCount = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            $this->_docCount += $segInfo->count();
        }
    }

    /**
     * Commit changes resulting from delete() or undeleteAll() operations.
     *
     * @todo undeleteAll processing.
     */
    public function commit()
    {
        if ($this->_hasChanges) {
            $this->_getIndexWriter()->commit();

            $this->_updateDocCount();

            $this->_hasChanges = false;
        }
    }


    /**
     * Optimize index.
     *
     * Merges all segments into one
     */
    public function optimize()
    {
        // Commit changes if any changes have been made
        $this->commit();

        if (count($this->_segmentInfos) > 1 || $this->hasDeletions()) {
            $this->_getIndexWriter()->optimize();
            $this->_updateDocCount();
        }
    }


    /**
     * Returns an array of all terms in this index.
     *
     * @return array
     */
    public function terms()
    {
        $result = array();

        $segmentInfoQueue = new Index\TermsPriorityQueue();

        foreach ($this->_segmentInfos as $segmentInfo) {
            $segmentInfo->resetTermsStream();

            // Skip "empty" segments
            if ($segmentInfo->currentTerm() !== null) {
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        while (($segmentInfo = $segmentInfoQueue->pop()) !== null) {
            if ($segmentInfoQueue->top() === null ||
                $segmentInfoQueue->top()->currentTerm()->key() !=
                            $segmentInfo->currentTerm()->key()) {
                // We got new term
                $result[] = $segmentInfo->currentTerm();
            }

            if ($segmentInfo->nextTerm() !== null) {
                // Put segment back into the priority queue
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        return $result;
    }


    /**
     * Terms stream priority queue object
     *
     * @var \ZendSearch\Lucene\TermStreamsPriorityQueue
     */
    private $_termsStream = null;

    /**
     * Reset terms stream.
     */
    public function resetTermsStream()
    {
        if ($this->_termsStream === null) {
            $this->_termsStream = new TermStreamsPriorityQueue($this->_segmentInfos);
        } else {
            $this->_termsStream->resetTermsStream();
        }
    }

    /**
     * Skip terms stream up to specified term preffix.
     *
     * Prefix contains fully specified field info and portion of searched term
     *
     * @param \ZendSearch\Lucene\Index\Term $prefix
     */
    public function skipTo(Index\Term $prefix)
    {
        $this->_termsStream->skipTo($prefix);
    }

    /**
     * Scans terms dictionary and returns next term
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function nextTerm()
    {
        return $this->_termsStream->nextTerm();
    }

    /**
     * Returns term in current position
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function currentTerm()
    {
        return $this->_termsStream->currentTerm();
    }

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream()
    {
        $this->_termsStream->closeTermsStream();
        $this->_termsStream = null;
    }


    /*************************************************************************
    @todo UNIMPLEMENTED
    *************************************************************************/
    /**
     * Undeletes all documents currently marked as deleted in this index.
     *
     * @todo Implementation
     */
    public function undeleteAll()
    {}
}
