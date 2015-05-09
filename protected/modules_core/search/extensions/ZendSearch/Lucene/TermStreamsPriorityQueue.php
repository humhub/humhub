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

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class TermStreamsPriorityQueue implements Index\TermsStreamInterface
{
    /**
     * Array of term streams (ZendSearch\Lucene\Index\TermsStreamInterface objects)
     *
     * @var array
     */
    protected $_termStreams;

    /**
     * Terms stream queue
     *
     * @var \ZendSearch\Lucene\Index\TermsPriorityQueue
     */
    protected $_termsStreamQueue = null;

    /**
     * Last Term in a terms stream
     *
     * @var \ZendSearch\Lucene\Index\Term
     */
    protected $_lastTerm = null;


    /**
     * Object constructor
     *
     * @param array $termStreams  array of term streams (\ZendSearch\Lucene\Index\TermsStreamInterface objects)
     */
    public function __construct(array $termStreams)
    {
        $this->_termStreams = $termStreams;

        $this->resetTermsStream();
    }

    /**
     * Reset terms stream.
     */
    public function resetTermsStream()
    {
        $this->_termsStreamQueue = new Index\TermsPriorityQueue();

        foreach ($this->_termStreams as $termStream) {
            $termStream->resetTermsStream();

            // Skip "empty" containers
            if ($termStream->currentTerm() !== null) {
                $this->_termsStreamQueue->put($termStream);
            }
        }

        $this->nextTerm();
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
        $termStreams = array();

        while (($termStream = $this->_termsStreamQueue->pop()) !== null) {
            $termStreams[] = $termStream;
        }

        foreach ($termStreams as $termStream) {
            $termStream->skipTo($prefix);

            if ($termStream->currentTerm() !== null) {
                $this->_termsStreamQueue->put($termStream);
            }
        }

        $this->nextTerm();
    }

    /**
     * Scans term streams and returns next term
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function nextTerm()
    {
        while (($termStream = $this->_termsStreamQueue->pop()) !== null) {
            if ($this->_termsStreamQueue->top() === null ||
                $this->_termsStreamQueue->top()->currentTerm()->key() !=
                            $termStream->currentTerm()->key()) {
                // We got new term
                $this->_lastTerm = $termStream->currentTerm();

                if ($termStream->nextTerm() !== null) {
                    // Put segment back into the priority queue
                    $this->_termsStreamQueue->put($termStream);
                }

                return $this->_lastTerm;
            }

            if ($termStream->nextTerm() !== null) {
                // Put segment back into the priority queue
                $this->_termsStreamQueue->put($termStream);
            }
        }

        // End of stream
        $this->_lastTerm = null;

        return null;
    }

    /**
     * Returns term in current position
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function currentTerm()
    {
        return $this->_lastTerm;
    }

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream()
    {
        while (($termStream = $this->_termsStreamQueue->pop()) !== null) {
            $termStream->closeTermsStream();
        }

        $this->_termsStreamQueue = null;
        $this->_lastTerm         = null;
    }
}
