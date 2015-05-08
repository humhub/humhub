<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use Zend\Http\Request;

/**
 * Allow Header
 *
 * @link       http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
 */
class Allow implements HeaderInterface
{
    /**
     * List of request methods
     * true states that method is allowed, false - disallowed
     * By default GET and POST are allowed
     *
     * @var array
     */
    protected $methods = array(
        Request::METHOD_OPTIONS => false,
        Request::METHOD_GET     => true,
        Request::METHOD_HEAD    => false,
        Request::METHOD_POST    => true,
        Request::METHOD_PUT     => false,
        Request::METHOD_DELETE  => false,
        Request::METHOD_TRACE   => false,
        Request::METHOD_CONNECT => false,
        Request::METHOD_PATCH   => false,
    );

    /**
     * Create Allow header from header line
     *
     * @param string $headerLine
     * @return Allow
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString($headerLine)
    {
        $header = new static();

        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'allow') {
            throw new Exception\InvalidArgumentException('Invalid header line for Allow string: "' . $name . '"');
        }

        // reset list of methods
        $header->methods = array_fill_keys(array_keys($header->methods), false);

        // allow methods from header line
        foreach (explode(',', $value) as $method) {
            $method = trim(strtoupper($method));
            $header->methods[$method] = true;
        }

        return $header;
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Allow';
    }

    /**
     * Get comma-separated list of allowed methods
     *
     * @return string
     */
    public function getFieldValue()
    {
        return implode(', ', array_keys($this->methods, true, true));
    }

    /**
     * Get list of all defined methods
     *
     * @return array
     */
    public function getAllMethods()
    {
        return $this->methods;
    }

    /**
     * Get list of allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array_keys($this->methods, true, true);
    }

    /**
     * Allow methods or list of methods
     *
     * @param array|string $allowedMethods
     * @return Allow
     */
    public function allowMethods($allowedMethods)
    {
        foreach ((array) $allowedMethods as $method) {
            $method = trim(strtoupper($method));
            $this->methods[$method] = true;
        }

        return $this;
    }

    /**
     * Disallow methods or list of methods
     *
     * @param array|string $disallowedMethods
     * @return Allow
     */
    public function disallowMethods($disallowedMethods)
    {
        foreach ((array) $disallowedMethods as $method) {
            $method = trim(strtoupper($method));
            $this->methods[$method] = false;
        }

        return $this;
    }

    /**
     * Convenience alias for @see disallowMethods()
     *
     * @param array|string $disallowedMethods
     * @return Allow
     */
    public function denyMethods($disallowedMethods)
    {
        return $this->disallowMethods($disallowedMethods);
    }

    /**
     * Check whether method is allowed
     *
     * @param string $method
     * @return bool
     */
    public function isAllowedMethod($method)
    {
        $method = trim(strtoupper($method));

        // disallow unknown method
        if (! isset($this->methods[$method])) {
            $this->methods[$method] = false;
        }

        return $this->methods[$method];
    }

    /**
     * Return header as string
     *
     * @return string
     */
    public function toString()
    {
        return 'Allow: ' . $this->getFieldValue();
    }
}
