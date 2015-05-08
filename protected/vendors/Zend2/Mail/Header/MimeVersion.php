<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;

class MimeVersion implements HeaderInterface
{
    /**
     * @var string Version string
     */
    protected $version = '1.0';

    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'mime-version') {
            throw new Exception\InvalidArgumentException('Invalid header line for MIME-Version string');
        }

        // Check for version, and set if found
        $header = new static();
        if (preg_match('/^(?P<version>\d+\.\d+)$/', $value, $matches)) {
            $header->setVersion($matches['version']);
        }

        return $header;
    }

    public function getFieldName()
    {
        return 'MIME-Version';
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        return $this->version;
    }

    public function setEncoding($encoding)
    {
        // This header must be always in US-ASCII
        return $this;
    }

    public function getEncoding()
    {
        return 'ASCII';
    }

    public function toString()
    {
        return 'MIME-Version: ' . $this->getFieldValue();
    }

    /**
     * Set the version string used in this header
     *
     * @param  string $version
     * @return MimeVersion
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Retrieve the version string for this header
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
