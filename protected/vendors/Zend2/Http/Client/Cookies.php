<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Client;

use ArrayIterator;
use Traversable;
use Zend\Http\Header\SetCookie;
use Zend\Http\Response;
use Zend\Stdlib\ArrayUtils;
use Zend\Uri;

/**
 * A Cookies object is designed to contain and maintain HTTP cookies, and should
 * be used along with Zend\Http\Client in order to manage cookies across HTTP requests and
 * responses.
 *
 * The class contains an array of Zend\Http\Header\Cookie objects. Cookies can be added
 * automatically from a request or manually. Then, the Cookies class can find and return the
 * cookies needed for a specific HTTP request.
 *
 * A special parameter can be passed to all methods of this class that return cookies: Cookies
 * can be returned either in their native form (as Zend\Http\Header\Cookie objects) or as strings -
 * the later is suitable for sending as the value of the "Cookie" header in an HTTP request.
 * You can also choose, when returning more than one cookie, whether to get an array of strings
 * (by passing Zend\Http\Client\Cookies::COOKIE_STRING_ARRAY) or one unified string for all cookies
 * (by passing Zend\Http\Client\Cookies::COOKIE_STRING_CONCAT).
 *
 * @link       http://wp.netscape.com/newsref/std/cookie_spec.html for some specs.
 */
class Cookies
{
    /**
     * Return cookie(s) as a Zend\Http\Header\Cookie object
     *
     */
    const COOKIE_OBJECT = 0;

    /**
     * Return cookie(s) as a string (suitable for sending in an HTTP request)
     *
     */
    const COOKIE_STRING_ARRAY = 1;

    /**
     * Return all cookies as one long string (suitable for sending in an HTTP request)
     *
     */
    const COOKIE_STRING_CONCAT = 2;

    /**
     * Array storing cookies
     *
     * Cookies are stored according to domain and path:
     * $cookies
     *  + www.mydomain.com
     *    + /
     *      - cookie1
     *      - cookie2
     *    + /somepath
     *      - othercookie
     *  + www.otherdomain.net
     *    + /
     *      - alsocookie
     *
     * @var array
     */
    protected $cookies = array();

    /**
     * The Zend\Http\Header\Cookie array
     *
     * @var array
     */
    protected $rawCookies = array();

    /**
     * Add a cookie to the class. Cookie should be passed either as a Zend\Http\Header\Cookie object
     * or as a string - in which case an object is created from the string.
     *
     * @param SetCookie|string $cookie
     * @param Uri\Uri|string    $refUri Optional reference URI (for domain, path, secure)
     * @throws Exception\InvalidArgumentException if invalid $cookie value
     */
    public function addCookie($cookie, $refUri = null)
    {
        if (is_string($cookie)) {
            $cookie = SetCookie::fromString($cookie, $refUri);
        }

        if ($cookie instanceof SetCookie) {
            $domain = $cookie->getDomain();
            $path   = $cookie->getPath();
            if (!isset($this->cookies[$domain])) {
                $this->cookies[$domain] = array();
            }
            if (!isset($this->cookies[$domain][$path])) {
                $this->cookies[$domain][$path] = array();
            }
            $this->cookies[$domain][$path][$cookie->getName()] = $cookie;
            $this->rawCookies[] = $cookie;
        } else {
            throw new Exception\InvalidArgumentException('Supplient argument is not a valid cookie string or object');
        }
    }

    /**
     * Parse an HTTP response, adding all the cookies set in that response
     *
     * @param Response $response
     * @param Uri\Uri|string $refUri Requested URI
     */
    public function addCookiesFromResponse(Response $response, $refUri)
    {
        $cookieHdrs = $response->getHeaders()->get('Set-Cookie');
        if ($cookieHdrs instanceof Traversable) {
            $cookieHdrs = ArrayUtils::iteratorToArray($cookieHdrs);
        }

        if (is_array($cookieHdrs)) {
            foreach ($cookieHdrs as $cookie) {
                $this->addCookie($cookie, $refUri);
            }
        } elseif (is_string($cookieHdrs)) {
            $this->addCookie($cookieHdrs, $refUri);
        }
    }

    /**
     * Get all cookies in the cookie jar as an array
     *
     * @param int $retAs Whether to return cookies as objects of \Zend\Http\Header\Cookie or as strings
     * @return array|string
     */
    public function getAllCookies($retAs = self::COOKIE_OBJECT)
    {
        $cookies = $this->_flattenCookiesArray($this->cookies, $retAs);
        return $cookies;
    }

    /**
     * Return an array of all cookies matching a specific request according to the request URI,
     * whether session cookies should be sent or not, and the time to consider as "now" when
     * checking cookie expiry time.
     *
     * @param string|Uri\Uri $uri URI to check against (secure, domain, path)
     * @param bool $matchSessionCookies Whether to send session cookies
     * @param int $retAs Whether to return cookies as objects of \Zend\Http\Header\Cookie or as strings
     * @param int $now Override the current time when checking for expiry time
     * @throws Exception\InvalidArgumentException if invalid URI
     * @return array|string
     */
    public function getMatchingCookies($uri, $matchSessionCookies = true,
        $retAs = self::COOKIE_OBJECT, $now = null)
    {
        if (is_string($uri)) {
            $uri = Uri\UriFactory::factory($uri, 'http');
        } elseif (!$uri instanceof Uri\Uri) {
            throw new Exception\InvalidArgumentException("Invalid URI string or object passed");
        }

        $host = $uri->getHost();
        if (empty($host)) {
            throw new Exception\InvalidArgumentException('Invalid URI specified; does not contain a host');
        }

        // First, reduce the array of cookies to only those matching domain and path
        $cookies = $this->_matchDomain($host);
        $cookies = $this->_matchPath($cookies, $uri->getPath());
        $cookies = $this->_flattenCookiesArray($cookies, self::COOKIE_OBJECT);

        // Next, run Cookie->match on all cookies to check secure, time and session matching
        $ret = array();
        foreach ($cookies as $cookie)
            if ($cookie->match($uri, $matchSessionCookies, $now))
                $ret[] = $cookie;

        // Now, use self::_flattenCookiesArray again - only to convert to the return format ;)
        $ret = $this->_flattenCookiesArray($ret, $retAs);

        return $ret;
    }

    /**
     * Get a specific cookie according to a URI and name
     *
     * @param Uri\Uri|string $uri The uri (domain and path) to match
     * @param string $cookieName The cookie's name
     * @param int $retAs Whether to return cookies as objects of \Zend\Http\Header\Cookie or as strings
     * @throws Exception\InvalidArgumentException if invalid URI specified or invalid $retAs value
     * @return Cookie|string
     */
    public function getCookie($uri, $cookieName, $retAs = self::COOKIE_OBJECT)
    {
        if (is_string($uri)) {
            $uri = Uri\UriFactory::factory($uri, 'http');
        } elseif (!$uri instanceof Uri\Uri) {
            throw new Exception\InvalidArgumentException('Invalid URI specified');
        }

        $host = $uri->getHost();
        if (empty($host)) {
            throw new Exception\InvalidArgumentException('Invalid URI specified; host missing');
        }

        // Get correct cookie path
        $path = $uri->getPath();
        $path = substr($path, 0, strrpos($path, '/'));
        if (! $path) $path = '/';

        if (isset($this->cookies[$uri->getHost()][$path][$cookieName])) {
            $cookie = $this->cookies[$uri->getHost()][$path][$cookieName];

            switch ($retAs) {
                case self::COOKIE_OBJECT:
                    return $cookie;
                    break;

                case self::COOKIE_STRING_ARRAY:
                case self::COOKIE_STRING_CONCAT:
                    return $cookie->__toString();
                    break;

                default:
                    throw new Exception\InvalidArgumentException("Invalid value passed for \$retAs: {$retAs}");
                    break;
            }
        }

        return false;
    }

    /**
     * Helper function to recursively flatten an array. Should be used when exporting the
     * cookies array (or parts of it)
     *
     * @param \Zend\Http\Header\Cookie|array $ptr
     * @param int $retAs What value to return
     * @return array|string
     */
    protected function _flattenCookiesArray($ptr, $retAs = self::COOKIE_OBJECT)
    {
        if (is_array($ptr)) {
            $ret = ($retAs == self::COOKIE_STRING_CONCAT ? '' : array());
            foreach ($ptr as $item) {
                if ($retAs == self::COOKIE_STRING_CONCAT) {
                    $ret .= $this->_flattenCookiesArray($item, $retAs);
                } else {
                    $ret = array_merge($ret, $this->_flattenCookiesArray($item, $retAs));
                }
            }
            return $ret;
        } elseif ($ptr instanceof SetCookie) {
            switch ($retAs) {
                case self::COOKIE_STRING_ARRAY:
                    return array($ptr->__toString());
                    break;

                case self::COOKIE_STRING_CONCAT:
                    return $ptr->__toString();
                    break;

                case self::COOKIE_OBJECT:
                default:
                    return array($ptr);
                    break;
            }
        }

        return null;
    }

    /**
     * Return a subset of the cookies array matching a specific domain
     *
     * @param string $domain
     * @return array
     */
    protected function _matchDomain($domain)
    {
        $ret = array();

        foreach (array_keys($this->cookies) as $cdom) {
            if (SetCookie::matchCookieDomain($cdom, $domain)) {
                $ret[$cdom] = $this->cookies[$cdom];
            }
        }

        return $ret;
    }

    /**
     * Return a subset of a domain-matching cookies that also match a specified path
     *
     * @param array $domains
     * @param string $path
     * @return array
     */
    protected function _matchPath($domains, $path)
    {
        $ret = array();

        foreach ($domains as $dom => $pathsArray) {
            foreach (array_keys($pathsArray) as $cpath) {
                if (SetCookie::matchCookiePath($cpath, $path)) {
                    if (! isset($ret[$dom])) {
                        $ret[$dom] = array();
                    }

                    $ret[$dom][$cpath] = $pathsArray[$cpath];
                }
            }
        }

        return $ret;
    }

    /**
     * Create a new Cookies object and automatically load into it all the
     * cookies set in an Http_Response object. If $uri is set, it will be
     * considered as the requested URI for setting default domain and path
     * of the cookie.
     *
     * @param Response $response HTTP Response object
     * @param Uri\Uri|string $refUri The requested URI
     * @return Cookies
     * @todo Add the $uri functionality.
     */
    public static function fromResponse(Response $response, $refUri)
    {
        $jar = new static();
        $jar->addCookiesFromResponse($response, $refUri);
        return $jar;
    }

    /**
     * Required by Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->rawCookies);
    }

    /**
     * Required by IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->rawCookies);
    }

    /**
     * Tells if the array of cookies is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this) == 0;
    }

    /**
     * Empties the cookieJar of any cookie
     *
     * @return Cookies
     */
    public function reset()
    {
        $this->cookies = $this->rawCookies = array();
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool Returns true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or &null;
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return mixed the original value unserialized.
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    public function fromArray(array $values)
    {
        // TODO: Implement fromArray() method.
    }

    public function fromString($string)
    {
        // TODO: Implement fromString() method.
    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }

    public function toString()
    {
        // TODO: Implement toString() method.
    }

    public function get($name, $default = null)
    {
        // TODO: Implement get() method.
    }

    public function set($name, $value)
    {
        // TODO: Implement set() method.
    }
}
