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
use Zend\Feed\Uri;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\ArrayUtils;

class Publisher
{
    /**
     * An array of URLs for all Hub Servers used by the Publisher, and to
     * which all topic update notifications will be sent.
     *
     * @var array
     */
    protected $hubUrls = array();

    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $updatedTopicUrls = array();

    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Zend\Http\Response object.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Constructor; accepts an array or Zend\Config\Config instance to preset
     * options for the Publisher without calling all supported setter
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
     * @param  array|Traversable $options Options array or Traversable object
     * @return Publisher
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
        if (array_key_exists('updatedTopicUrls', $options)) {
            $this->addUpdatedTopicUrls($options['updatedTopicUrls']);
        }
        if (array_key_exists('parameters', $options)) {
            $this->setParameters($options['parameters']);
        }
        return $this;
    }

    /**
     * Add a Hub Server URL supported by Publisher
     *
     * @param  string $url
     * @return Publisher
     * @throws Exception\InvalidArgumentException
     */
    public function addHubUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . 'URL');
        }
        $this->hubUrls[] = $url;
        return $this;
    }

    /**
     * Add an array of Hub Server URLs supported by Publisher
     *
     * @param  array $urls
     * @return Publisher
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
     * @return Publisher
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
     * Add a URL to a topic (Atom or RSS feed) which has been updated
     *
     * @param  string $url
     * @return Publisher
     * @throws Exception\InvalidArgumentException
     */
    public function addUpdatedTopicUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . 'URL');
        }
        $this->updatedTopicUrls[] = $url;
        return $this;
    }

    /**
     * Add an array of Topic URLs which have been updated
     *
     * @param  array $urls
     * @return Publisher
     */
    public function addUpdatedTopicUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->addUpdatedTopicUrl($url);
        }
        return $this;
    }

    /**
     * Remove an updated topic URL
     *
     * @param  string $url
     * @return Publisher
     */
    public function removeUpdatedTopicUrl($url)
    {
        if (!in_array($url, $this->getUpdatedTopicUrls())) {
            return $this;
        }
        $key = array_search($url, $this->updatedTopicUrls);
        unset($this->updatedTopicUrls[$key]);
        return $this;
    }

    /**
     * Return an array of unique updated topic URLs currently available
     *
     * @return array
     */
    public function getUpdatedTopicUrls()
    {
        $this->updatedTopicUrls = array_unique($this->updatedTopicUrls);
        return $this->updatedTopicUrls;
    }

    /**
     * Notifies a single Hub Server URL of changes
     *
     * @param  string $url The Hub Server's URL
     * @return void
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function notifyHub($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . 'URL');
        }
        $client = $this->_getHttpClient();
        $client->setUri($url);
        $response = $client->getResponse();
        if ($response->getStatusCode() !== 204) {
            throw new Exception\RuntimeException('Notification to Hub Server '
                . 'at "' . $url . '" appears to have failed with a status code of "'
                . $response->getStatusCode() . '" and message "'
                . $response->getContent() . '"');
        }
    }

    /**
     * Notifies all Hub Server URLs of changes
     *
     * If a Hub notification fails, certain data will be retained in an
     * an array retrieved using getErrors(), if a failure occurs for any Hubs
     * the isSuccess() check will return FALSE. This method is designed not
     * to needlessly fail with an Exception/Error unless from Zend\Http\Client.
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    public function notifyAll()
    {
        $client = $this->_getHttpClient();
        $hubs   = $this->getHubUrls();
        if (empty($hubs)) {
            throw new Exception\RuntimeException('No Hub Server URLs'
                . ' have been set so no notifications can be sent');
        }
        $this->errors = array();
        foreach ($hubs as $url) {
            $client->setUri($url);
            $response = $client->getResponse();
            if ($response->getStatusCode() !== 204) {
                $this->errors[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            }
        }
    }

    /**
     * Add an optional parameter to the update notification requests
     *
     * @param  string $name
     * @param  string|null $value
     * @return Publisher
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
     * Add an optional parameter to the update notification requests
     *
     * @param  array $parameters
     * @return Publisher
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
        return $this;
    }

    /**
     * Remove an optional parameter for the notification requests
     *
     * @param  string $name
     * @return Publisher
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
     * Return an array of optional parameters for notification requests
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a boolean indicator of whether the notifications to Hub
     * Servers were ALL successful. If even one failed, FALSE is returned.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return !(count($this->errors) != 0);
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
     * Get a basic prepared HTTP client for use
     *
     * @return \Zend\Http\Client
     * @throws Exception\RuntimeException
     */
    protected function _getHttpClient()
    {
        $client = PubSubHubbub::getHttpClient();
        $client->setMethod(HttpRequest::METHOD_POST);
        $client->setOptions(array(
            'useragent' => 'Zend_Feed_Pubsubhubbub_Publisher/' . Version::VERSION,
        ));
        $params   = array();
        $params[] = 'hub.mode=publish';
        $topics   = $this->getUpdatedTopicUrls();
        if (empty($topics)) {
            throw new Exception\RuntimeException('No updated topic URLs'
                . ' have been set');
        }
        foreach ($topics as $topicUrl) {
            $params[] = 'hub.url=' . urlencode($topicUrl);
        }
        $optParams = $this->getParameters();
        foreach ($optParams as $name => $value) {
            $params[] = urlencode($name) . '=' . urlencode($value);
        }
        $paramString = implode('&', $params);
        $client->setRawBody($paramString);
        return $client;
    }
}
