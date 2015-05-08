<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol\Smtp\Auth;

use Zend\Crypt\Hmac;
use Zend\Mail\Protocol\Smtp;

/**
 * Performs CRAM-MD5 authentication
 */
class Crammd5 extends Smtp
{
    /**
     * @var string
     */
    protected $username;


    /**
     * @var string
     */
    protected $password;


    /**
     * Constructor.
     *
     * All parameters may be passed as an array to the first argument of the
     * constructor. If so,
     *
     * @param  string|array $host   (Default: 127.0.0.1)
     * @param  null|int     $port   (Default: null)
     * @param  null|array   $config Auth-specific parameters
     */
    public function __construct($host = '127.0.0.1', $port = null, $config = null)
    {
        // Did we receive a configuration array?
        $origConfig = $config;
        if (is_array($host)) {
            // Merge config array with principal array, if provided
            if (is_array($config)) {
                $config = array_replace_recursive($host, $config);
            } else {
                $config = $host;
            }
        }

        if (is_array($config)) {
            if (isset($config['username'])) {
                $this->setUsername($config['username']);
            }
            if (isset($config['password'])) {
                $this->setPassword($config['password']);
            }
        }

        // Call parent with original arguments
        parent::__construct($host, $port, $origConfig);
    }


    /**
     * Performs CRAM-MD5 authentication with supplied credentials
     */
    public function auth()
    {
        // Ensure AUTH has not already been initiated.
        parent::auth();

        $this->_send('AUTH CRAM-MD5');
        $challenge = $this->_expect(334);
        $challenge = base64_decode($challenge);
        $digest = $this->_hmacMd5($this->getPassword(), $challenge);
        $this->_send(base64_encode($this->getUsername() . ' ' . $digest));
        $this->_expect(235);
        $this->auth = true;
    }

    /**
     * Set value for username
     *
     * @param  string $username
     * @return Crammd5
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set value for password
     *
     * @param  string $password
     * @return Crammd5
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Prepare CRAM-MD5 response to server's ticket
     *
     * @param  string $key   Challenge key (usually password)
     * @param  string $data  Challenge data
     * @param  int    $block Length of blocks (deprecated; unused)
     * @return string
     */
    protected function _hmacMd5($key, $data, $block = 64)
    {
        return Hmac::compute($key, 'md5', $data);
    }
}
