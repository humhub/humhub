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

/**
 * A Zend_Search_Lucene_Index_TermInfo represents a record of information stored for a term.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class TermInfo
{
    /**
     * The number of documents which contain the term.
     *
     * @var integer
     */
    public $docFreq;

    /**
     * Data offset in a Frequencies file.
     *
     * @var integer
     */
    public $freqPointer;

    /**
     * Data offset in a Positions file.
     *
     * @var integer
     */
    public $proxPointer;

    /**
     * ScipData offset in a Frequencies file.
     *
     * @var integer
     */
    public $skipOffset;

    /**
     * Term offset of the _next_ term in a TermDictionary file.
     * Used only for Term Index
     *
     * @var integer
     */
    public $indexPointer;

    public function __construct($docFreq, $freqPointer, $proxPointer, $skipOffset, $indexPointer = null)
    {
        $this->docFreq      = $docFreq;
        $this->freqPointer  = $freqPointer;
        $this->proxPointer  = $proxPointer;
        $this->skipOffset   = $skipOffset;
        $this->indexPointer = $indexPointer;
    }
}

