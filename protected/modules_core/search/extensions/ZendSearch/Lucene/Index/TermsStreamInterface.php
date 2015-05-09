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
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
interface TermsStreamInterface
{
    /**
     * Reset terms stream.
     */
    public function resetTermsStream();

    /**
     * Skip terms stream up to specified term preffix.
     *
     * Prefix contains fully specified field info and portion of searched term
     *
     * @param \ZendSearch\Lucene\Index\Term $prefix
     */
    public function skipTo(Term $prefix);

    /**
     * Scans terms dictionary and returns next term
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function nextTerm();

    /**
     * Returns term in current position
     *
     * @return \ZendSearch\Lucene\Index\Term|null
     */
    public function currentTerm();

    /**
     * Close terms stream
     *
     * Should be used for resources clean up if stream is not read up to the end
     */
    public function closeTermsStream();
}
