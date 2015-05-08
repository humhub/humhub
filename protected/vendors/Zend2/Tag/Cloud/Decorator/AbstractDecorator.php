<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag\Cloud\Decorator;

use Traversable;
use Zend\Escaper\Escaper;
use Zend\Stdlib\ArrayUtils;
use Zend\Tag\Cloud\Decorator\DecoratorInterface as Decorator;
use Zend\Tag\Exception;

/**
 * Abstract class for decorators
 */
abstract class AbstractDecorator implements Decorator
{
    /**
     * @var string Encoding to use
     */
    protected $encoding = 'UTF-8';

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'config',
    );

    /**
     * Create a new decorator with options
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for the decorator
     * @return AbstractTag
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->skipOptions)) {
                continue;
            }

            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set encoding
     *
     * @param string
     * @return HTMLCloud
     */
    public function setEncoding($value)
    {
        $this->encoding = (string) $value;
        return $this;
    }

    /**
     * Set Escaper instance
     *
     * @param  Escaper $escaper
     * @return HtmlCloud
     */
    public function setEscaper($escaper)
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * Retrieve Escaper instance
     *
     * If none registered, instantiates and registers one using current encoding.
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        if (null === $this->escaper) {
            $this->setEscaper(new Escaper($this->getEncoding()));
        }
        return $this->escaper;
    }

    /**
     * Validate an HTML element name
     *
     * @param  string $name
     * @throws Exception\InvalidElementNameException
     */
    protected function validateElementName($name)
    {
        if (!preg_match('/^[a-z0-9]+$/i', $name)) {
            throw new Exception\InvalidElementNameException(sprintf(
                '%s: Invalid element name "%s" provided; please provide valid HTML element names',
                __METHOD__,
                $this->getEscaper()->escapeHtml($name)
            ));
        }
    }

    /**
     * Validate an HTML attribute name
     *
     * @param  string $name
     * @throws Exception\InvalidAttributeNameException
     */
    protected function validateAttributeName($name)
    {
        if (!preg_match('/^[a-z_:][-a-z0-9_:.]*$/i', $name)) {
            throw new Exception\InvalidAttributeNameException(sprintf(
                '%s: Invalid HTML attribute name "%s" provided; please provide valid HTML attribute names',
                __METHOD__,
                $this->getEscaper()->escapeHtml($name)
            ));
        }
    }

    protected function wrapTag($html)
    {
        $escaper = $this->getEscaper();
        foreach ($this->getHTMLTags() as $key => $data) {
            if (is_array($data)) {
                $attributes = '';
                $htmlTag    = $key;
                $this->validateElementName($htmlTag);

                foreach ($data as $param => $value) {
                    $this->validateAttributeName($param);
                    $attributes .= ' ' . $param . '="' . $escaper->escapeHtmlAttr($value) . '"';
                }
            } else {
                $attributes = '';
                $htmlTag    = $data;
                $this->validateElementName($htmlTag);
            }

            $html = sprintf('<%1$s%3$s>%2$s</%1$s>', $htmlTag, $html, $attributes);
        }
        return $html;
    }
}
