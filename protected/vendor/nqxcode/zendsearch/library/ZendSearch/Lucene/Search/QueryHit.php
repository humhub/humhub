<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Document;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class QueryHit
{
    /**
     * Object handle of the index
     * @var \ZendSearch\Lucene\SearchIndexInterface
     */
    protected $_index = null;

    /**
     * Object handle of the document associated with this hit
     * @var \ZendSearch\Lucene\Document
     */
    protected $_document = null;

    /**
     * Unique hit id
     * @var integer
     */
    public $id;

    /**
     * Number of the document in the index
     * @var integer
     */
    public $document_id;

    /**
     * Score of the hit
     * @var float
     */
    public $score;


    /**
     * Constructor - pass object handle of Zend_Search_Lucene_Interface index that produced
     * the hit so the document can be retrieved easily from the hit.
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     */

    public function __construct(Lucene\SearchIndexInterface $index)
    {
        $this->_index = $index;
    }

    /**
     * Magic method for checking the existence of a field
     *
     * @param string $offset
     * @return boolean TRUE if the field exists else FALSE
     */
    public function __isset($offset)
    {
        return isset($this->getDocument()->$offset);
    }


    /**
     * Convenience function for getting fields from the document
     * associated with this hit.
     *
     * @param string $offset
     * @return string
     */
    public function __get($offset)
    {
        return $this->getDocument()->getFieldValue($offset);
    }


    /**
     * Return the document object for this hit
     *
     * @return \ZendSearch\Lucene\Document
     */
    public function getDocument()
    {
        if (!$this->_document instanceof Document) {
            $this->_document = $this->_index->getDocument($this->document_id);
        }

        return $this->_document;
    }


    /**
     * Return the index object for this hit
     *
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public function getIndex()
    {
        return $this->_index;
    }
}
