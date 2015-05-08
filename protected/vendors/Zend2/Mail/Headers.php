<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail;

use ArrayIterator;
use Countable;
use Iterator;
use Traversable;
use Zend\Loader\PluginClassLocator;

/**
 * Basic mail headers collection functionality
 *
 * Handles aggregation of headers
 */
class Headers implements Countable, Iterator
{
    /** @var string End of Line for fields */
    const EOL = "\r\n";

    /** @var string Start of Line when folding */
    const FOLDING = "\r\n ";

    /**
     * @var \Zend\Loader\PluginClassLoader
     */
    protected $pluginClassLoader = null;

    /**
     * @var array key names for $headers array
     */
    protected $headersKeys = array();

    /**
     * @var  Header\HeaderInterface[] instances
     */
    protected $headers = array();

    /**
     * Header encoding; defaults to ASCII
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    /**
     * Populates headers from string representation
     *
     * Parses a string for headers, and aggregates them, in order, in the
     * current instance, primarily as strings until they are needed (they
     * will be lazy loaded)
     *
     * @param  string $string
     * @param  string $EOL EOL string; defaults to {@link EOL}
     * @throws Exception\RuntimeException
     * @return Headers
     */
    public static function fromString($string, $EOL = self::EOL)
    {
        $headers     = new static();
        $currentLine = '';

        // iterate the header lines, some might be continuations
        foreach (explode($EOL, $string) as $line) {
            // check if a header name is present
            if (preg_match('/^(?P<name>[\x21-\x39\x3B-\x7E]+):.*$/', $line, $matches)) {
                if ($currentLine) {
                    // a header name was present, then store the current complete line
                    $headers->addHeaderLine($currentLine);
                }
                $currentLine = trim($line);
            } elseif (preg_match('/^\s+.*$/', $line, $matches)) {
                // continuation: append to current line
                $currentLine .= trim($line);
            } elseif (preg_match('/^\s*$/', $line)) {
                // empty line indicates end of headers
                break;
            } else {
                // Line does not match header format!
                throw new Exception\RuntimeException(sprintf(
                    'Line "%s"does not match header format!',
                    $line
                ));
            }
        }
        if ($currentLine) {
            $headers->addHeaderLine($currentLine);
        }
        return $headers;
    }

    /**
     * Set an alternate implementation for the PluginClassLoader
     *
     * @param  PluginClassLocator $pluginClassLoader
     * @return Headers
     */
    public function setPluginClassLoader(PluginClassLocator $pluginClassLoader)
    {
        $this->pluginClassLoader = $pluginClassLoader;
        return $this;
    }

    /**
     * Return an instance of a PluginClassLocator, lazyload and inject map if necessary
     *
     * @return PluginClassLocator
     */
    public function getPluginClassLoader()
    {
        if ($this->pluginClassLoader === null) {
            $this->pluginClassLoader = new Header\HeaderLoader();
        }
        return $this->pluginClassLoader;
    }

    /**
     * Set the header encoding
     *
     * @param  string $encoding
     * @return Headers
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        foreach ($this as $header) {
            $header->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Get the header encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Add many headers at once
     *
     * Expects an array (or Traversable object) of type/value pairs.
     *
     * @param  array|Traversable $headers
     * @throws Exception\InvalidArgumentException
     * @return Headers
     */
    public function addHeaders($headers)
    {
        if (!is_array($headers) && !$headers instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or Traversable; received "%s"',
                (is_object($headers) ? get_class($headers) : gettype($headers))
            ));
        }

        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                if (is_string($value)) {
                    $this->addHeaderLine($value);
                } elseif (is_array($value) && count($value) == 1) {
                    $this->addHeaderLine(key($value), current($value));
                } elseif (is_array($value) && count($value) == 2) {
                    $this->addHeaderLine($value[0], $value[1]);
                } elseif ($value instanceof Header\HeaderInterface) {
                    $this->addHeader($value);
                }
            } elseif (is_string($name)) {
                $this->addHeaderLine($name, $value);
            }

        }

        return $this;
    }

    /**
     * Add a raw header line, either in name => value, or as a single string 'name: value'
     *
     * This method allows for lazy-loading in that the parsing and instantiation of HeaderInterface object
     * will be delayed until they are retrieved by either get() or current()
     *
     * @throws Exception\InvalidArgumentException
     * @param  string $headerFieldNameOrLine
     * @param  string $fieldValue optional
     * @return Headers
     */
    public function addHeaderLine($headerFieldNameOrLine, $fieldValue = null)
    {
        if (!is_string($headerFieldNameOrLine)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects its first argument to be a string; received "%s"',
                (is_object($headerFieldNameOrLine) ? get_class($headerFieldNameOrLine) : gettype($headerFieldNameOrLine))
            ));
        }

        if ($fieldValue === null) {
            $this->addHeader(Header\GenericHeader::fromString($headerFieldNameOrLine));
        } elseif (is_array($fieldValue)) {
            foreach ($fieldValue as $i) {
                $this->addHeader(new Header\GenericMultiHeader($headerFieldNameOrLine, $i));
            }
        } else {
            $this->addHeader(new Header\GenericHeader($headerFieldNameOrLine, $fieldValue));
        }

        return $this;
    }

    /**
     * Add a Header\Interface to this container, for raw values see {@link addHeaderLine()} and {@link addHeaders()}
     *
     * @param  Header\HeaderInterface $header
     * @return Headers
     */
    public function addHeader(Header\HeaderInterface $header)
    {
        $key = $this->normalizeFieldName($header->getFieldName());
        $this->headersKeys[] = $key;
        $this->headers[] = $header;
        if ($this->getEncoding() !== 'ASCII') {
            $header->setEncoding($this->getEncoding());
        }
        return $this;
    }

    /**
     * Remove a Header from the container
     *
     * @param  string $fieldName
     * @return bool
     */
    public function removeHeader($fieldName)
    {
        $key = $this->normalizeFieldName($fieldName);
        $index = array_search($key, $this->headersKeys, true);
        if ($index !== false) {
            unset($this->headersKeys[$index]);
            unset($this->headers[$index]);
            return true;
        }
        return false;
    }

    /**
     * Clear all headers
     *
     * Removes all headers from queue
     *
     * @return Headers
     */
    public function clearHeaders()
    {
        $this->headers = $this->headersKeys = array();
        return $this;
    }

    /**
     * Get all headers of a certain name/type
     *
     * @param  string $name
     * @return bool|ArrayIterator|Header\HeaderInterface Returns false if there is no headers with $name in this
     * contain, an ArrayIterator if the header is a MultipleHeadersInterface instance and finally returns
     * HeaderInterface for the rest of cases.
     */
    public function get($name)
    {
        $key = $this->normalizeFieldName($name);
        $results = array();

        foreach (array_keys($this->headersKeys, $key) as $index) {
            if ($this->headers[$index] instanceof Header\GenericHeader) {
                $results[] = $this->lazyLoadHeader($index);
            } else {
                $results[] = $this->headers[$index];
            }
        }

        switch (count($results)) {
            case 0:
                return false;
            case 1:
                if ($results[0] instanceof Header\MultipleHeadersInterface) {
                    return new ArrayIterator($results);
                } else {
                    return $results[0];
                }
            default:
                return new ArrayIterator($results);
        }
    }

    /**
     * Test for existence of a type of header
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        $name = $this->normalizeFieldName($name);
        return in_array($name, $this->headersKeys);
    }

    /**
     * Advance the pointer for this object as an iterator
     *
     */
    public function next()
    {
        next($this->headers);
    }

    /**
     * Return the current key for this object as an iterator
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->headers);
    }

    /**
     * Is this iterator still valid?
     *
     * @return bool
     */
    public function valid()
    {
        return (current($this->headers) !== false);
    }

    /**
     * Reset the internal pointer for this object as an iterator
     *
     */
    public function rewind()
    {
        reset($this->headers);
    }

    /**
     * Return the current value for this iterator, lazy loading it if need be
     *
     * @return Header\HeaderInterface
     */
    public function current()
    {
        $current = current($this->headers);
        if ($current instanceof Header\GenericHeader) {
            $current = $this->lazyLoadHeader(key($this->headers));
        }
        return $current;
    }

    /**
     * Return the number of headers in this contain, if all headers have not been parsed, actual count could
     * increase if MultipleHeader objects exist in the Request/Response.  If you need an exact count, iterate
     *
     * @return int count of currently known headers
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Render all headers at once
     *
     * This method handles the normal iteration of headers; it is up to the
     * concrete classes to prepend with the appropriate status/request line.
     *
     * @return string
     */
    public function toString()
    {
        $headers = '';
        foreach ($this as $header) {
            if ($str = $header->toString()) {
                $headers .= $str . self::EOL;
            }
        }

        return $headers;
    }

    /**
     * Return the headers container as an array
     *
     * @todo determine how to produce single line headers, if they are supported
     * @return array
     */
    public function toArray()
    {
        $headers = array();
        /* @var $header Header\HeaderInterface */
        foreach ($this->headers as $header) {
            if ($header instanceof Header\MultipleHeadersInterface) {
                $name = $header->getFieldName();
                if (!isset($headers[$name])) {
                    $headers[$name] = array();
                }
                $headers[$name][] = $header->getFieldValue();
            } else {
                $headers[$header->getFieldName()] = $header->getFieldValue();
            }
        }
        return $headers;
    }

    /**
     * By calling this, it will force parsing and loading of all headers, after this count() will be accurate
     *
     * @return bool
     */
    public function forceLoading()
    {
        foreach ($this as $item) {
            // $item should now be loaded
        }
        return true;
    }

    /**
     * @param $index
     * @return mixed
     */
    protected function lazyLoadHeader($index)
    {
        $current = $this->headers[$index];

        $key = $this->headersKeys[$index];
        /* @var $class Header\HeaderInterface */
        $class = ($this->getPluginClassLoader()->load($key)) ?: 'Zend\Mail\Header\GenericHeader';

        $encoding = $current->getEncoding();
        $headers  = $class::fromString($current->toString());
        if (is_array($headers)) {
            $current = array_shift($headers);
            $current->setEncoding($encoding);
            $this->headers[$index] = $current;
            foreach ($headers as $header) {
                $header->setEncoding($encoding);
                $this->headersKeys[] = $key;
                $this->headers[]     = $header;
            }
            return $current;
        }

        $current = $headers;
        $current->setEncoding($encoding);
        $this->headers[$index] = $current;
        return $current;
    }

    /**
     * Normalize a field name
     *
     * @param  string $fieldName
     * @return string
     */
    protected function normalizeFieldName($fieldName)
    {
        return str_replace(array('-', '_', ' ', '.'), '', strtolower($fieldName));
    }
}
