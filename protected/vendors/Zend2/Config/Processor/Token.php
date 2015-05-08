<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Processor;

use Traversable;
use Zend\Config\Config;
use Zend\Config\Exception;

class Token implements ProcessorInterface
{
    /**
     * Token prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Token suffix.
     *
     * @var string
     */
    protected $suffix = '';

    /**
     * The registry of tokens
     *
     * @var array
     */
    protected $tokens = array();

    /**
     * Replacement map
     *
     * @var array
     */
    protected $map = null;

    /**
     * Token Processor walks through a Config structure and replaces all
     * occurrences of tokens with supplied values.
     *
     * @param  array|Config|Traversable   $tokens  Associative array of TOKEN => value
     *                                             to replace it with
     * @param    string $prefix
     * @param    string $suffix
     * @internal param array $options
     * @return   Token
     */
    public function __construct($tokens = array(), $prefix = '', $suffix = '')
    {
        $this->setTokens($tokens);
        $this->setPrefix($prefix);
        $this->setSuffix($suffix);
    }

    /**
     * @param  string $prefix
     * @return Token
     */
    public function setPrefix($prefix)
    {
        // reset map
        $this->map = null;
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param  string $suffix
     * @return Token
     */
    public function setSuffix($suffix)
    {
        // reset map
        $this->map = null;
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Set token registry.
     *
     * @param  array|Config|Traversable  $tokens  Associative array of TOKEN => value
     *                                            to replace it with
     * @return Token
     * @throws Exception\InvalidArgumentException
     */
    public function setTokens($tokens)
    {
        if (is_array($tokens)) {
            $this->tokens = $tokens;
        } elseif ($tokens instanceof Config) {
            $this->tokens = $tokens->toArray();
        } elseif ($tokens instanceof Traversable) {
            $this->tokens = array();
            foreach ($tokens as $key => $val) {
                $this->tokens[$key] = $val;
            }
        } else {
            throw new Exception\InvalidArgumentException('Cannot use ' . gettype($tokens) . ' as token registry.');
        }

        // reset map
        $this->map = null;

        return $this;
    }

    /**
     * Get current token registry.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Add new token.
     *
     * @param  string $token
     * @param  mixed $value
     * @return Token
     * @throws Exception\InvalidArgumentException
     */
    public function addToken($token, $value)
    {
        if (!is_scalar($token)) {
            throw new Exception\InvalidArgumentException('Cannot use ' . gettype($token) . ' as token name.');
        }
        $this->tokens[$token] = $value;

        // reset map
        $this->map = null;

        return $this;
    }

    /**
     * Add new token.
     *
     * @param string $token
     * @param mixed $value
     * @return Token
     */
    public function setToken($token, $value)
    {
        return $this->addToken($token, $value);
    }

    /**
     * Build replacement map
     */
    protected function buildMap()
    {
        if (!$this->suffix && !$this->prefix) {
            $this->map = $this->tokens;
        } else {
            $this->map = array();
            foreach ($this->tokens as $token => $value) {
                $this->map[$this->prefix . $token . $this->suffix] = $value;
            }
        }
    }

    /**
     * Process
     *
     * @param  Config $config
     * @return Config
     * @throws Exception\InvalidArgumentException
     */
    public function process(Config $config)
    {
        if ($config->isReadOnly()) {
            throw new Exception\InvalidArgumentException('Cannot process config because it is read-only');
        }

        if ($this->map === null) {
            $this->buildMap();
        }

        /**
         * Walk through config and replace values
         */
        $keys = array_keys($this->map);
        $values = array_values($this->map);
        foreach ($config as $key => $val) {
            if ($val instanceof Config) {
                $this->process($val);
            } else {
                $config->$key = str_replace($keys, $values, $val);
            }
        }

        return $config;
    }

    /**
     * Process a single value
     *
     * @param $value
     * @return mixed
     */
    public function processValue($value)
    {
        if ($this->map === null) {
            $this->buildMap();
        }
        $keys = array_keys($this->map);
        $values = array_values($this->map);

        return str_replace($keys, $values, $value);
    }
}
