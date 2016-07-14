<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Analysis\Analyzer\Common;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Analysis;
use ZendSearch\Lucene\Exception\RuntimeException;
use Zend\Stdlib\ErrorHandler;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 */
class Utf8Num extends AbstractCommon
{
    /**
     * Current char position in an UTF-8 stream
     *
     * @var integer
     */
    private $_position;

    /**
     * Current binary position in an UTF-8 stream
     *
     * @var integer
     */
    private $_bytePosition;

    /**
     * Object constructor
     *
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    public function __construct()
    {
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/\pL/u', 'a');
        ErrorHandler::stop();
        if ($result != 1) {
            // PCRE unicode support is turned off
            throw new RuntimeException('Utf8Num analyzer needs PCRE unicode support to be enabled.');
        }
    }

    /**
     * Reset token stream
     */
    public function reset()
    {
        $this->_position     = 0;
        $this->_bytePosition = 0;

        // convert input into UTF-8
        if (strcasecmp($this->_encoding, 'utf8' ) != 0  &&
            strcasecmp($this->_encoding, 'utf-8') != 0 ) {
                $this->_input = iconv($this->_encoding, 'UTF-8', $this->_input);
                $this->_encoding = 'UTF-8';
        }
    }

    /**
     * Tokenization stream API
     * Get next token
     * Returns null at the end of stream
     *
     * @return \ZendSearch\Lucene\Analysis\Token|null
     */
    public function nextToken()
    {
        if ($this->_input === null) {
            return null;
        }

        do {
            if (! preg_match('/[\p{L}\p{N}]+/u', $this->_input, $match, PREG_OFFSET_CAPTURE, $this->_bytePosition)) {
                // It covers both cases a) there are no matches (preg_match(...) === 0)
                // b) error occured (preg_match(...) === FALSE)
                return null;
            }

            // matched string
            $matchedWord = $match[0][0];

            // binary position of the matched word in the input stream
            $binStartPos = $match[0][1];

            // character position of the matched word in the input stream
            $startPos = $this->_position +
                        iconv_strlen(substr($this->_input,
                                            $this->_bytePosition,
                                            $binStartPos - $this->_bytePosition),
                                     'UTF-8');
            // character postion of the end of matched word in the input stream
            $endPos = $startPos + iconv_strlen($matchedWord, 'UTF-8');

            $this->_bytePosition = $binStartPos + strlen($matchedWord);
            $this->_position     = $endPos;

            $token = $this->normalize(new Analysis\Token($matchedWord, $startPos, $endPos));
        } while ($token === null); // try again if token is skipped

        return $token;
    }
}

