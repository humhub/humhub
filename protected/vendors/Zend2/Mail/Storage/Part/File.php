<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Storage\Part;

use Zend\Mail\Headers;
use Zend\Mail\Storage\Part;

class File extends Part
{
    protected $contentPos = array();
    protected $partPos = array();
    protected $fh;

    /**
     * Public constructor
     *
     * This handler supports the following params:
     * - file     filename or open file handler with message content (required)
     * - startPos start position of message or part in file (default: current position)
     * - endPos   end position of message or part in file (default: end of file)
     *
     * @param   array $params  full message with or without headers
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $params)
    {
        if (empty($params['file'])) {
            throw new Exception\InvalidArgumentException('no file given in params');
        }

        if (!is_resource($params['file'])) {
            $this->fh = fopen($params['file'], 'r');
        } else {
            $this->fh = $params['file'];
        }
        if (!$this->fh) {
            throw new Exception\RuntimeException('could not open file');
        }
        if (isset($params['startPos'])) {
            fseek($this->fh, $params['startPos']);
        }
        $header = '';
        $endPos = isset($params['endPos']) ? $params['endPos'] : null;
        while (($endPos === null || ftell($this->fh) < $endPos) && trim($line = fgets($this->fh))) {
            $header .= $line;
        }

        $this->headers = Headers::fromString($header);

        $this->contentPos[0] = ftell($this->fh);
        if ($endPos !== null) {
            $this->contentPos[1] = $endPos;
        } else {
            fseek($this->fh, 0, SEEK_END);
            $this->contentPos[1] = ftell($this->fh);
        }
        if (!$this->isMultipart()) {
            return;
        }

        $boundary = $this->getHeaderField('content-type', 'boundary');
        if (!$boundary) {
            throw new Exception\RuntimeException('no boundary found in content type to split message');
        }

        $part = array();
        $pos = $this->contentPos[0];
        fseek($this->fh, $pos);
        while (!feof($this->fh) && ($endPos === null || $pos < $endPos)) {
            $line = fgets($this->fh);
            if ($line === false) {
                if (feof($this->fh)) {
                    break;
                }
                throw new Exception\RuntimeException('error reading file');
            }

            $lastPos = $pos;
            $pos = ftell($this->fh);
            $line = trim($line);

            if ($line == '--' . $boundary) {
                if ($part) {
                    // not first part
                    $part[1] = $lastPos;
                    $this->partPos[] = $part;
                }
                $part = array($pos);
            } elseif ($line == '--' . $boundary . '--') {
                $part[1] = $lastPos;
                $this->partPos[] = $part;
                break;
            }
        }
        $this->countParts = count($this->partPos);

    }


    /**
     * Body of part
     *
     * If part is multipart the raw content of this part with all sub parts is returned
     *
     * @param resource $stream Optional
     * @return string body
     */
    public function getContent($stream = null)
    {
        fseek($this->fh, $this->contentPos[0]);
        if ($stream !== null) {
            return stream_copy_to_stream($this->fh, $stream, $this->contentPos[1] - $this->contentPos[0]);
        }
        $length = $this->contentPos[1] - $this->contentPos[0];
        return $length < 1 ? '' : fread($this->fh, $length);
    }

    /**
     * Return size of part
     *
     * Quite simple implemented currently (not decoding). Handle with care.
     *
     * @return int size
     */
    public function getSize()
    {
        return $this->contentPos[1] - $this->contentPos[0];
    }

    /**
     * Get part of multipart message
     *
     * @param  int $num number of part starting with 1 for first part
     * @throws Exception\RuntimeException
     * @return Part wanted part
     */
    public function getPart($num)
    {
        --$num;
        if (!isset($this->partPos[$num])) {
            throw new Exception\RuntimeException('part not found');
        }

        return new static(array('file' => $this->fh, 'startPos' => $this->partPos[$num][0],
                              'endPos' => $this->partPos[$num][1]));
    }
}
