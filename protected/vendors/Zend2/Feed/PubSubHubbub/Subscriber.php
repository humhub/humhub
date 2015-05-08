<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\PubSubHubbub;

use DateInterval;
use DateTime;
use Traversable;
use Zend\Feed\Uri;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\ArrayUtils;

class Subscriber
{
    /**
     * An array of URLs for all Hub Servers to subscribe/unsubscribe.
     *
     * @var array
     */
    protected $hubUrls = array();

    /**
     * An array of optional parameters to be included in any
     * (un)subscribe requests.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * The URL of the topic (Rss or Atom feed) which is the subject of
     * our current intent to subscribe to/unsubscribe from updates from
     * the currently configured Hub Servers.
     *
     * @var string
     */
    protected $topicUrl = '';

    /**
     * The URL Hub Servers must use when communicating with this Subscriber
     *
     * @var string
     */
    protected $callbackUrl = '';

    /**
     * The number of seconds for which the subscriber would like to have the
     * subscription active. Defaults to null, i.e. not sent, to setup a
     * permanent subscription if possible.
     *
     * @var int
     */
    protected $leaseSeconds = null;

    /**
     * The preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but is considered
     * desirable to support asynchronous verification if possible.
     *
     * Zend\Feed\Pubsubhubbub\Subscriber will always send both modes, whose
     * order of occurrence in the parameter list determines this preference.
     *
     * @var string
     */
    protected $preferredVerificationMode = PubSubHubbub::VERIFICATION_MODE_SYNC;

    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Zend\Http\Response object.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * An array of Hub Server URLs for Hubs operating at this time in
     * asynchronous verification mode.
     *
     * @var array
     */
    protected $asyncHubs = array();

    /**
     * An instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @var \Zend\Feed\PubSubHubbub\Model\SubscriptionPersistenceInterface
     */
    protected $storage = null;

    /**
     * An array of authentication credentials for HTTP Basic Authentication
     * if required by specific Hubs. The array is indexed by Hub Endpoint URI
     * and the value is a simple array of the username and password to apply.
     *
     * @var array
     */
    protected $authentications = array();

    /**
     * Tells the Subscriber to append any subscription identifier to the path
     * of the base Callback URL. E.g. an identifier "subkey1" would be added
     * to the callback URL "http://www.example.com/callback" to create a subscription
     * specific Callback URL of "http://www.example.com/callback/subkey1".
     *
     * This is required for all Hubs using the Pubsubhubbub 0.1 Specification.
     * It should be manually intercepted and passed to the Callback class using
     * Zend\Feed\Pubsubhubbub\Subscriber\Callback::setSubscriptionKey(). Will
     * require a route in the form "callback/:subkey" to allow the parameter be
     * retrieved from an action using the Zend\Controller\Action::\getParam()
     * method.
     *
     * @var string
     */
    protected $usePathParameter = false;

    /**
     * Constructor; accepts an array or Traversable instance to preset
     * options for the Subscriber without calling all supported setter
     * methods in turn.
     *
     * @param  array|Traversable $options
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
     * @param  array|Traversable $options
     * @return Subscriber
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
        if (array_key_exists('hubUrls', $options)) {
            $this->addHubUrls($options['hubUrls']);
        }
        if (array_key_exists('callbackUrl', $options)) {
            $this->setCallbackUrl($options['callbackUrl']);
        }
        if (array_key_exists('topicUrl', $options)) {
            $this->setTopicUrl($options['topicUrl']);
        }
        if (array_key_exists('storage', $options)) {
            $this->setStorage($options['storage']);
        }
        if (array_key_exists('leaseSeconds', $options)) {
            $this->setLeaseSeconds($options['leaseSeconds']);
        }
        if (array_key_exists('parameters', $options)) {
            $this->setParameters($options['parameters']);
        }
        if (array_key_exists('authentications', $options)) {
            $this->addAuthentications($options['authentications']);
        }
        if (array_key_exists('usePathParameter', $options)) {
            $this->usePathParameter($options['usePathParameter']);
        }
        if (array_key_exists('preferredVerificationMode', $options)) {
            $this->setPreferredVerificationMode(
                $options['preferredVerificationMode']
            );
        }
        return $this;
    }

    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @param  string $url
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function setTopicUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .' URL');
        }
        $this->topicUrl = $url;
        return $this;
    }

    /**
     * Set the topic URL (RSS or Atom feed) to which the intended (un)subscribe
     * event will relate
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function getTopicUrl()
    {
        if (empty($this->topicUrl)) {
            throw new Exception\RuntimeException('A valid Topic (RSS or Atom'
                . ' feed) URL MUST be set before attempting any operation');
        }
        return $this->topicUrl;
    }

    /**
     * Set the number of seconds for which any subscription will remain valid
     *
     * @param  int $seconds
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function setLeaseSeconds($seconds)
    {
        $seconds = intval($seconds);
        if ($seconds <= 0) {
            throw new Exception\InvalidArgumentException('Expected lease seconds'
                . ' must be an integer greater than zero');
        }
        $this->leaseSeconds = $seconds;
        return $this;
    }

    /**
     * Get the number of lease seconds on subscriptions
     *
     * @return int
     */
    public function getLeaseSeconds()
    {
        return $this->leaseSeconds;
    }

    /**
     * Set the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @param  string $url
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function setCallbackUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . ' URL');
        }
        $this->callbackUrl = $url;
        return $this;
    }

    /**
     * Get the callback URL to be used by Hub Servers when communicating with
     * this Subscriber
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function getCallbackUrl()
    {
        if (empty($this->callbackUrl)) {
            throw new Exception\RuntimeException('A valid Callback URL MUST be'
                . ' set before attempting any operation');
        }
        return $this->callbackUrl;
    }

    /**
     * Set preferred verification mode (sync or async). By default, this
     * Subscriber prefers synchronous verification, but does support
     * asynchronous if that's the Hub Server's utilised mode.
     *
     * Zend\Feed\Pubsubhubbub\Subscriber will always send both modes, whose
     * order of occurrence in the parameter list determines this preference.
     *
     * @param  string $mode Should be 'sync' or 'async'
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function setPreferredVerificationMode($mode)
    {
        if ($mode !== PubSubHubbub::VERIFICATION_MODE_SYNC
            && $mode !== PubSubHubbub::VERIFICATION_MODE_ASYNC
        ) {
            throw new Exception\InvalidArgumentException('Invalid preferred'
                . ' mode specified: "' . $mode . '" but should be one of'
                . ' Zend\Feed\Pubsubhubbub::VERIFICATION_MODE_SYNC or'
                . ' Zend\Feed\Pubsubhubbub::VERIFICATION_MODE_ASYNC');
        }
        $this->preferredVerificationMode = $mode;
        return $this;
    }

    /**
     * Get preferred verification mode (sync or async).
     *
     * @return string
     */
    public function getPreferredVerificationMode()
    {
        return $this->preferredVerificationMode;
    }

    /**
     * Add a Hub Server URL supported by Publisher
     *
     * @param  string $url
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function addHubUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . ' URL');
        }
        $this->hubUrls[] = $url;
        return $this;
    }

    /**
     * Add an array of Hub Server URLs supported by Publisher
     *
     * @param  array $urls
     * @return Subscriber
     */
    public function addHubUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHubUrl($url);
        }
        return $this;
    }

    /**
     * Remove a Hub Server URL
     *
     * @param  string $url
     * @return Subscriber
     */
    public function removeHubUrl($url)
    {
        if (!in_array($url, $this->getHubUrls())) {
            return $this;
        }
        $key = array_search($url, $this->hubUrls);
        unset($this->hubUrls[$key]);
        return $this;
    }

    /**
     * Return an array of unique Hub Server URLs currently available
     *
     * @return array
     */
    public function getHubUrls()
    {
        $this->hubUrls = array_unique($this->hubUrls);
        return $this->hubUrls;
    }

    /**
     * Add authentication credentials for a given URL
     *
     * @param  string $url
     * @param  array $authentication
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function addAuthentication($url, array $authentication)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . ' URL');
        }
        $this->authentications[$url] = $authentication;
        return $this;
    }

    /**
     * Add authentication credentials for hub URLs
     *
     * @param  array $authentications
     * @return Subscriber
     */
    public function addAuthentications(array $authentications)
    {
        foreach ($authentications as $url => $authentication) {
            $this->addAuthentication($url, $authentication);
        }
        return $this;
    }

    /**
     * Get all hub URL authentication credentials
     *
     * @return array
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * Set flag indicating whether or not to use a path parameter
     *
     * @param  bool $bool
     * @return Subscriber
     */
    public function usePathParameter($bool = true)
    {
        $this->usePathParameter = $bool;
        return $this;
    }

    /**
     * Add an optional parameter to the (un)subscribe requests
     *
     * @param  string $name
     * @param  string|null $value
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function setParameter($name, $value = null)
    {
        if (is_array($name)) {
            $this->setParameters($name);
            return $this;
        }
        if (empty($name) || !is_string($name)) {
            throw new Exception\InvalidArgumentException('Invalid parameter "name"'
                . ' of "' . $name . '" must be a non-empty string');
        }
        if ($value === null) {
            $this->removeParameter($name);
            return $this;
        }
        if (empty($value) || (!is_string($value) && $value !== null)) {
            throw new Exception\InvalidArgumentException('Invalid parameter "value"'
                . ' of "' . $value . '" must be a non-empty string');
        }
        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Add an optional parameter to the (un)subscribe requests
     *
     * @param  array $parameters
     * @return Subscriber
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
        return $this;
    }

    /**
     * Remove an optional parameter for the (un)subscribe requests
     *
     * @param  string $name
     * @return Subscriber
     * @throws Exception\InvalidArgumentException
     */
    public function removeParameter($name)
    {
        if (empty($name) || !is_string($name)) {
            throw new Exception\InvalidArgumentException('Invalid parameter "name"'
                . ' of "' . $name . '" must be a non-empty string');
        }
        if (array_key_exists($name, $this->parameters)) {
            unset($this->parameters[$name]);
        }
        return $this;
    }

    /**
     * Return an array of optional parameters for (un)subscribe requests
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets an instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @param  Model\SubscriptionPersistenceInterface $storage
     * @return Subscriber
     */
    public function setStorage(Model\SubscriptionPersistenceInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Gets an instance of Zend\Feed\Pubsubhubbub\Storage\StoragePersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return Model\SubscriptionPersistenceInterface
     * @throws Exception\RuntimeException
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            throw new Exception\RuntimeException('No storage vehicle '
                . 'has been set.');
        }
        return $this->storage;
    }

    /**
     * Subscribe to one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     * @return void
     */
    public function subscribeAll()
    {
        $this->_doRequest('subscribe');
    }

    /**
     * Unsubscribe from one or more Hub Servers using the stored Hub URLs
     * for the given Topic URL (RSS or Atom feed)
     *
     * @return void
     */
    public function unsubscribeAll()
    {
        $this->_doRequest('unsubscribe');
    }

    /**
     * Returns a boolean indicator of whether the notifications to Hub
     * Servers were ALL successful. If even one failed, FALSE is returned.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Return an array of errors met from any failures, including keys:
     * 'response' => the Zend\Http\Response object from the failure
     * 'hubUrl' => the URL of the Hub Server whose notification failed
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return an array of Hub Server URLs who returned a response indicating
     * operation in Asynchronous Verification Mode, i.e. they will not confirm
     * any (un)subscription immediately but at a later time (Hubs may be
     * doing this as a batch process when load balancing)
     *
     * @return array
     */
    public function getAsyncHubs()
    {
        return $this->asyncHubs;
    }

    /**
     * Executes an (un)subscribe request
     *
     * @param  string $mode
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function _doRequest($mode)
    {
        $client = $this->_getHttpClient();
        $hubs   = $this->getHubUrls();
        if (empty($hubs)) {
            throw new Exception\RuntimeException('No Hub Server URLs'
                . ' have been set so no subscriptions can be attempted');
        }
        $this->errors = array();
        $this->asyncHubs = array();
        foreach ($hubs as $url) {
            if (array_key_exists($url, $this->authentications)) {
                $auth = $this->authentications[$url];
                $client->setAuth($auth[0], $auth[1]);
            }
            $client->setUri($url);
            $client->setRawBody($params = $this->_getRequestParameters($url, $mode));
            $response = $client->send();
            if ($response->getStatusCode() !== 204
                && $response->getStatusCode() !== 202
            ) {
                $this->errors[] = array(
                    'response' => $response,
                    'hubUrl'   => $url,
                );
            /**
             * At first I thought it was needed, but the backend storage will
             * allow tracking async without any user interference. It's left
             * here in case the user is interested in knowing what Hubs
             * are using async verification modes so they may update Models and
             * move these to asynchronous processes.
             */
            } elseif ($response->getStatusCode() == 202) {
                $this->asyncHubs[] = array(
                    'response' => $response,
                    'hubUrl'   => $url,
                );
            }
        }
    }

    /**
     * Get a basic prepared HTTP client for use
     *
     * @return \Zend\Http\Client
     */
    protected function _getHttpClient()
    {
        $client = PubSubHubbub::getHttpClient();
        $client->setMethod(HttpRequest::METHOD_POST);
        $client->setOptions(array('useragent' => 'Zend_Feed_Pubsubhubbub_Subscriber/'
            . Version::VERSION));
        return $client;
    }

    /**
     * Return a list of standard protocol/optional parameters for addition to
     * client's POST body that are specific to the current Hub Server URL
     *
     * @param  string $hubUrl
     * @param  string $mode
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function _getRequestParameters($hubUrl, $mode)
    {
        if (!in_array($mode, array('subscribe', 'unsubscribe'))) {
            throw new Exception\InvalidArgumentException('Invalid mode specified: "'
                . $mode . '" which should have been "subscribe" or "unsubscribe"');
        }

        $params = array(
            'hub.mode'  => $mode,
            'hub.topic' => $this->getTopicUrl(),
        );

        if ($this->getPreferredVerificationMode()
                == PubSubHubbub::VERIFICATION_MODE_SYNC
        ) {
            $vmodes = array(
                PubSubHubbub::VERIFICATION_MODE_SYNC,
                PubSubHubbub::VERIFICATION_MODE_ASYNC,
            );
        } else {
            $vmodes = array(
                PubSubHubbub::VERIFICATION_MODE_ASYNC,
                PubSubHubbub::VERIFICATION_MODE_SYNC,
            );
        }
        $params['hub.verify'] = array();
        foreach ($vmodes as $vmode) {
            $params['hub.verify'][] = $vmode;
        }

        /**
         * Establish a persistent verify_token and attach key to callback
         * URL's path/query_string
         */
        $key   = $this->_generateSubscriptionKey($params, $hubUrl);
        $token = $this->_generateVerifyToken();
        $params['hub.verify_token'] = $token;

        // Note: query string only usable with PuSH 0.2 Hubs
        if (!$this->usePathParameter) {
            $params['hub.callback'] = $this->getCallbackUrl()
                . '?xhub.subscription=' . PubSubHubbub::urlencode($key);
        } else {
            $params['hub.callback'] = rtrim($this->getCallbackUrl(), '/')
                . '/' . PubSubHubbub::urlencode($key);
        }
        if ($mode == 'subscribe' && $this->getLeaseSeconds() !== null) {
            $params['hub.lease_seconds'] = $this->getLeaseSeconds();
        }

        // hub.secret not currently supported
        $optParams = $this->getParameters();
        foreach ($optParams as $name => $value) {
            $params[$name] = $value;
        }

        // store subscription to storage
        $now = new DateTime();
        $expires = null;
        if (isset($params['hub.lease_seconds'])) {
            $expires = $now->add(new DateInterval('PT' . $params['hub.lease_seconds'] . 'S'))
                ->format('Y-m-d H:i:s');
        }
        $data = array(
            'id'                 => $key,
            'topic_url'          => $params['hub.topic'],
            'hub_url'            => $hubUrl,
            'created_time'       => $now->format('Y-m-d H:i:s'),
            'lease_seconds'      => $params['hub.lease_seconds'],
            'verify_token'       => hash('sha256', $params['hub.verify_token']),
            'secret'             => null,
            'expiration_time'    => $expires,
            'subscription_state' => ($mode == 'unsubscribe')? PubSubHubbub::SUBSCRIPTION_TODELETE : PubSubHubbub::SUBSCRIPTION_NOTVERIFIED,
        );
        $this->getStorage()->setSubscription($data);

        return $this->_toByteValueOrderedString(
            $this->_urlEncode($params)
        );
    }

    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server. Follows no particular method, which means
     * it might be improved/changed in future.
     *
     * @return string
     */
    protected function _generateVerifyToken()
    {
        if (!empty($this->testStaticToken)) {
            return $this->testStaticToken;
        }
        return uniqid(rand(), true) . time();
    }

    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server.
     *
     * @param array   $params
     * @param string $hubUrl The Hub Server URL for which this token will apply
     * @return string
     */
    protected function _generateSubscriptionKey(array $params, $hubUrl)
    {
        $keyBase = $params['hub.topic'] . $hubUrl;
        $key     = md5($keyBase);

        return $key;
    }

    /**
     * URL Encode an array of parameters
     *
     * @param  array $params
     * @return array
     */
    protected function _urlEncode(array $params)
    {
        $encoded = array();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $ekey = PubSubHubbub::urlencode($key);
                $encoded[$ekey] = array();
                foreach ($value as $duplicateKey) {
                    $encoded[$ekey][]
                        = PubSubHubbub::urlencode($duplicateKey);
                }
            } else {
                $encoded[PubSubHubbub::urlencode($key)]
                    = PubSubHubbub::urlencode($value);
            }
        }
        return $encoded;
    }

    /**
     * Order outgoing parameters
     *
     * @param  array $params
     * @return array
     */
    protected function _toByteValueOrderedString(array $params)
    {
        $return = array();
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }

    /**
     * This is STRICTLY for testing purposes only...
     */
    protected $testStaticToken = null;

    final public function setTestStaticToken($token)
    {
        $this->testStaticToken = (string) $token;
    }
}
