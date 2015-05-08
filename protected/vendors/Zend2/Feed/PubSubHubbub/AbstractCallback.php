<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\PubSubHubbub;

use Traversable;
use Zend\Http\PhpEnvironment\Response as PhpResponse;
use Zend\Stdlib\ArrayUtils;

abstract class AbstractCallback implements CallbackInterface
{
    /**
     * An instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistenceInterface
     * used to background save any verification tokens associated with a subscription
     * or other.
     *
     * @var Model\SubscriptionPersistenceInterface
     */
    protected $storage = null;

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @var HttpResponse|PhpResponse
     */
    protected $httpResponse = null;

    /**
     * The number of Subscribers for which any updates are on behalf of.
     *
     * @var int
     */
    protected $subscriberCount = 1;

    /**
     * Constructor; accepts an array or Traversable object to preset
     * options for the Subscriber without calling all supported setter
     * methods in turn.
     *
     * @param  array|Traversable $options Options array or Traversable object
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Process any injected configuration options
     *
     * @param  array|Traversable $options Options array or Traversable object
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Array or Traversable object'
            . 'expected, got ' . gettype($options));
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        if (array_key_exists('storage', $options)) {
            $this->setStorage($options['storage']);
        }
        return $this;
    }

    /**
     * Send the response, including all headers.
     * If you wish to handle this via Zend\Http, use the getter methods
     * to retrieve any data needed to be set on your HTTP Response object, or
     * simply give this object the HTTP Response instance to work with for you!
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->getHttpResponse()->send();
    }

    /**
     * Sets an instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @param  Model\SubscriptionPersistenceInterface $storage
     * @return AbstractCallback
     */
    public function setStorage(Model\SubscriptionPersistenceInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Gets an instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return Model\SubscriptionPersistenceInterface
     * @throws Exception\RuntimeException
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            throw new Exception\RuntimeException('No storage object has been'
                . ' set that subclasses Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence');
        }
        return $this->storage;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @param  HttpResponse|PhpResponse $httpResponse
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setHttpResponse($httpResponse)
    {
        if (!$httpResponse instanceof HttpResponse && !$httpResponse instanceof PhpResponse) {
            throw new Exception\InvalidArgumentException('HTTP Response object must'
                . ' implement one of Zend\Feed\Pubsubhubbub\HttpResponse or'
                . ' Zend\Http\PhpEnvironment\Response');
        }
        $this->httpResponse = $httpResponse;
        return $this;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @return HttpResponse|PhpResponse
     */
    public function getHttpResponse()
    {
        if ($this->httpResponse === null) {
            $this->httpResponse = new HttpResponse;
        }
        return $this->httpResponse;
    }

    /**
     * Sets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     * Defaults to 1 if left unchanged.
     *
     * @param  string|int $count
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setSubscriberCount($count)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception\InvalidArgumentException('Subscriber count must be'
                . ' greater than zero');
        }
        $this->subscriberCount = $count;
        return $this;
    }

    /**
     * Gets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     *
     * @return int
     */
    public function getSubscriberCount()
    {
        return $this->subscriberCount;
    }

    /**
     * Attempt to detect the callback URL (specifically the path forward)
     * @return string
     */
    protected function _detectCallbackUrl()
    {
        $callbackUrl = '';
        if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
            $callbackUrl = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $callbackUrl = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $callbackUrl = $_SERVER['REQUEST_URI'];
            $scheme = 'http';
            if ($_SERVER['HTTPS'] == 'on') {
                $scheme = 'https';
            }
            $schemeAndHttpHost = $scheme . '://' . $this->_getHttpHost();
            if (strpos($callbackUrl, $schemeAndHttpHost) === 0) {
                $callbackUrl = substr($callbackUrl, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $callbackUrl= $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $callbackUrl .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $callbackUrl;
    }

    /**
     * Get the HTTP host
     *
     * @return string
     */
    protected function _getHttpHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        $scheme = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        }
        $name = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        if (($scheme == 'http' && $port == 80)
            || ($scheme == 'https' && $port == 443)
        ) {
            return $name;
        }

        return $name . ':' . $port;
    }

    /**
     * Retrieve a Header value from either $_SERVER or Apache
     *
     * @param string $header
     * @return bool|string
     */
    protected function _getHeader($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return the raw body of the request
     *
     * @return string|false Raw body, or false if not present
     */
    protected function _getRawBody()
    {
        $body = file_get_contents('php://input');
        if (strlen(trim($body)) == 0 && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $body = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if (strlen(trim($body)) > 0) {
            return $body;
        }
        return false;
    }
}
