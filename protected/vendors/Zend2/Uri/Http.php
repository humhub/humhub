<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Uri;

/**
 * HTTP URI handler
 */
class Http extends Uri
{
    /**
     * @see Uri::$validSchemes
     */
    protected static $validSchemes = array(
        'http',
        'https'
    );

    /**
     * @see Uri::$defaultPorts
     */
    protected static $defaultPorts = array(
        'http'  => 80,
        'https' => 443,
    );

    /**
     * @see Uri::$validHostTypes
     */
    protected $validHostTypes = self::HOST_DNS_OR_IPV4_OR_IPV6_OR_REGNAME;

    /**
     * User name as provided in authority of URI
     * @var null|string
     */
    protected $user;

    /**
     * Password as provided in authority of URI
     * @var null|string
     */
    protected $password;

    /**
     * Check if the URI is a valid HTTP URI
     *
     * This applies additional HTTP specific validation rules beyond the ones
     * required by the generic URI syntax
     *
     * @return bool
     * @see    Uri::isValid()
     */
    public function isValid()
    {
        return parent::isValid();
    }

    /**
     * Get the username part (before the ':') of the userInfo URI part
     *
     * @return null|string
     */
    public function getUser()
    {
        if (null !== $this->user) {
            return $this->user;
        }

        $this->parseUserInfo();
        return $this->user;
    }

    /**
     * Get the password part (after the ':') of the userInfo URI part
     *
     * @return string
     */
    public function getPassword()
    {
        if (null !== $this->password) {
            return $this->password;
        }

        $this->parseUserInfo();
        return $this->password;
    }

    /**
     * Set the username part (before the ':') of the userInfo URI part
     *
     * @param  string $user
     * @return Http
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the password part (after the ':') of the userInfo URI part
     *
     * @param  string $password
     * @return Http
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Validate the host part of an HTTP URI
     *
     * This overrides the common URI validation method with a DNS or IP only
     * default. Users may still enforce allowing other host types.
     *
     * @param  string  $host
     * @param  int $allowed
     * @return bool
     */
    public static function validateHost($host, $allowed = self::HOST_DNS_OR_IPV4_OR_IPV6)
    {
        return parent::validateHost($host, $allowed);
    }

    /**
     * Parse the user info into username and password segments
     *
     * Parses the user information into username and password segments, and
     * then sets the appropriate values.
     *
     * @return void
     */
    protected function parseUserInfo()
    {
        // No user information? we're done
        if (null === $this->userInfo) {
            return;
        }

        // If no ':' separator, we only have a username
        if (false === strpos($this->userInfo, ':')) {
            $this->setUser($this->userInfo);
            return;
        }

        // Split on the ':', and set both user and password
        list($user, $password) = explode(':', $this->userInfo, 2);
        $this->setUser($user);
        $this->setPassword($password);
    }

    /**
     * Return the URI port
     *
     * If no port is set, will return the default port according to the scheme
     *
     * @return int
     * @see    Zend\Uri\Uri::getPort()
     */
    public function getPort()
    {
        if (empty($this->port)) {
            if (array_key_exists($this->scheme, static::$defaultPorts)) {
                return static::$defaultPorts[$this->scheme];
            }
        }
        return $this->port;
    }

    /**
     * Parse a URI string
     *
     * @param  string $uri
     * @return Http
     */
    public function parse($uri)
    {
        parent::parse($uri);

        if (empty($this->path)) {
            $this->path = '/';
        }

        return $this;
    }
}
