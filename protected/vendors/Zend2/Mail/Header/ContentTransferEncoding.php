<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;

use Zend\Mail\Headers;

class ContentTransferEncoding implements HeaderInterface
{
    /**
     * Allowed Content-Transfer-Encoding parameters specified by RFC 1521
     * (reduced set)
     * @var array
     */
    protected static $allowedTransferEncodings = array(
        '7bit',
        '8bit',
        'quoted-printable',
        'base64',
        /*
         * not implemented:
         * 'binary',
         * x-token: 'X-'
         */
    );


    /**
     * @var string
     */
    protected $transferEncoding;

    /**
     * @var array
     */
    protected $parameters = array();

    public static function fromString($headerLine)
    {
        $headerLine = iconv_mime_decode($headerLine, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'content-transfer-encoding') {
            throw new Exception\InvalidArgumentException('Invalid header line for Content-Transfer-Encoding string');
        }

        $header = new static();
        $header->setTransferEncoding($value);

        return $header;
    }

    public function getFieldName()
    {
        return 'Content-Transfer-Encoding';
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return $this->transferEncoding;
    }

    public function setEncoding($encoding)
    {
        // Header must be always in US-ASCII
        return $this;
    }

    public function getEncoding()
    {
        return 'ASCII';
    }

    public function toString()
    {
        return 'Content-Transfer-Encoding: ' . $this->getFieldValue();
    }

    /**
     * Set the content transfer encoding
     *
     * @param  string $transferEncoding
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setTransferEncoding($transferEncoding)
    {
        if (!in_array($transferEncoding, self::$allowedTransferEncodings)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects one of "'. implode(', ', self::$allowedTransferEncodings) . '"; received "%s"',
                __METHOD__,
                (string) $transferEncoding
            ));
        }
        $this->transferEncoding = $transferEncoding;
        return $this;
    }

    /**
     * Retrieve the content transfer encoding
     *
     * @return string
     */
    public function getTransferEncoding()
    {
        return $this->transferEncoding;
    }
}
