<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class Item implements TaggableInterface
{
    /**
     * Title of the tag
     *
     * @var string
     */
    protected $title = null;

    /**
     * Weight of the tag
     *
     * @var float
     */
    protected $weight = null;

    /**
     * Custom parameters
     *
     * @var string
     */
    protected $params = array();

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'param'
    );

    /**
     * Create a new tag according to the options
     *
     * @param  array|Traversable $options
     * @throws \Zend\Tag\Exception\InvalidArgumentException When invalid options are provided
     * @throws \Zend\Tag\Exception\InvalidArgumentException When title was not set
     * @throws \Zend\Tag\Exception\InvalidArgumentException When weight was not set
     */
    public function __construct($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Invalid options provided to constructor');
        }

        $this->setOptions($options);

        if ($this->title === null) {
            throw new Exception\InvalidArgumentException('Title was not set');
        }

        if ($this->weight === null) {
            throw new Exception\InvalidArgumentException('Weight was not set');
        }
    }

    /**
     * Set options of the tag
     *
     * @param  array $options
     * @return \Zend\Tag\Item
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
     * Defined by Zend\Tag\TaggableInterface
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title
     *
     * @param  string $title
     * @throws \Zend\Tag\Exception\InvalidArgumentException When title is no string
     * @return \Zend\Tag\Item
     */
    public function setTitle($title)
    {
        if (!is_string($title)) {
            throw new Exception\InvalidArgumentException('Title must be a string');
        }

        $this->title = (string) $title;
        return $this;
    }

    /**
     * Defined by Zend\Tag\TaggableInterface
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set the weight
     *
     * @param  float $weight
     * @throws \Zend\Tag\Exception\InvalidArgumentException When weight is not numeric
     * @return \Zend\Tag\Item
     */
    public function setWeight($weight)
    {
        if (!is_numeric($weight)) {
            throw new Exception\InvalidArgumentException('Weight must be numeric');
        }

        $this->weight = (float) $weight;
        return $this;
    }

    /**
     * Set multiple params at once
     *
     * @param  array $params
     * @return \Zend\Tag\Item
     */
    public function setParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }

        return $this;
    }

    /**
     * Defined by Zend\Tag\TaggableInterface
     *
     * @param  string $name
     * @param  mixed  $value
     * @return \Zend\Tag\Item
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Defined by Zend\Tag\TaggableInterface
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }
}
