<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

use Traversable;
use Zend\Validator\AbstractValidator;

/**
 * Base class for Captcha adapters
 *
 * Provides some utility functionality to build on
 */
abstract class AbstractAdapter extends AbstractValidator implements AdapterInterface
{
    /**
     * Captcha name
     *
     * Useful to generate/check form fields
     *
     * @var string
     */
    protected $name;

    /**
     * Captcha options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Options to skip when processing options
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'config',
    );

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AbstractAdapter
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set single option for the object
     *
     * @param  string $key
     * @param  string $value
     * @return AbstractAdapter
     */
    public function setOption($key, $value)
    {
        if (in_array(strtolower($key), $this->skipOptions)) {
            return $this;
        }

        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            // Setter exists; use it
            $this->$method($value);
            $this->options[$key] = $value;
        } elseif (property_exists($this, $key)) {
            // Assume it's metadata
            $this->$key = $value;
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * Set object state from options array
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return AbstractAdapter
     */
    public function setOptions($options = array())
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get helper name used to render captcha
     *
     * By default, return empty string, indicating no helper needed.
     *
     * @return string
     */
    public function getHelperName()
    {
        return '';
    }
}
