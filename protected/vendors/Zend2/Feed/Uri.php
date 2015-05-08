<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed;

class Uri
{
    /**
     * @var string
     */
    protected $fragment;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var bool
     */
    protected $valid;

    /**
     * Valid schemes
     */
    protected $validSchemes = array(
        'http',
        'https',
        'file',
    );

    /**
     * @param  string $uri
     */
    public function __construct($uri)
    {
        $parsed = parse_url($uri);
        if (false === $parsed) {
            $this->valid = false;
            return;
        }

        $this->scheme   = isset($parsed['scheme'])   ? $parsed['scheme']   : null;
        $this->host     = isset($parsed['host'])     ? $parsed['host']     : null;
        $this->port     = isset($parsed['port'])     ? $parsed['port']     : null;
        $this->user     = isset($parsed['user'])     ? $parsed['user']     : null;
        $this->pass     = isset($parsed['pass'])     ? $parsed['pass']     : null;
        $this->path     = isset($parsed['path'])     ? $parsed['path']     : null;
        $this->query    = isset($parsed['query'])    ? $parsed['query']    : null;
        $this->fragment = isset($parsed['fragment']) ? $parsed['fragment'] : null;
    }

    /**
     * Create an instance
     *
     * Useful for chained validations
     *
     * @param  string $uri
     * @return self
     */
    public static function factory($uri)
    {
        return new static($uri);
    }

    /**
     * Retrieve the host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the URI path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Is the URI valid?
     *
     * @return bool
     */
    public function isValid()
    {
        if (false === $this->valid) {
            return false;
        }

        if ($this->scheme && !in_array($this->scheme, $this->validSchemes)) {
            return false;
        }

        if ($this->host) {
            if ($this->path && substr($this->path, 0, 1) != '/') {
                return false;
            }
            return true;
        }

        // no host, but user and/or port... what?
        if ($this->user || $this->port) {
            return false;
        }

        if ($this->path) {
            // Check path-only (no host) URI
            if (substr($this->path, 0, 2) == '//') {
                return false;
            }
            return true;
        }

        if (! ($this->query || $this->fragment)) {
            // No host, path, query or fragment - this is not a valid URI
            return false;
        }

        return true;
    }

    /**
     * Is the URI absolute?
     *
     * @return bool
     */
    public function isAbsolute()
    {
        return ($this->scheme !== null);
    }
}
