<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

/**
 * Accept Ranges Header
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.5
 */
class AcceptRanges implements HeaderInterface
{

    protected $rangeUnit = null;

    public static function fromString($headerLine)
    {
        $header = new static();

        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'accept-ranges') {
            throw new Exception\InvalidArgumentException('Invalid header line for Accept-Ranges string');
        }

        $header->rangeUnit = trim($value);

        return $header;
    }

    public function getFieldName()
    {
        return 'Accept-Ranges';
    }

    public function getFieldValue()
    {
        return $this->getRangeUnit();
    }

    public function setRangeUnit($rangeUnit)
    {
        $this->rangeUnit = $rangeUnit;
        return $this;
    }

    public function getRangeUnit()
    {
        return $this->rangeUnit;
    }

    public function toString()
    {
        return 'Accept-Ranges: ' . $this->getFieldValue();
    }
}
