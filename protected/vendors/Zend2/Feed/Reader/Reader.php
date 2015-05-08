<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

use DOMDocument;
use DOMXPath;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Http as ZendHttp;
use Zend\Stdlib\ErrorHandler;

/**
*/
class Reader
{
    /**
     * Namespace constants
     */
    const NAMESPACE_ATOM_03  = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10  = 'http://www.w3.org/2005/Atom';
    const NAMESPACE_RDF      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_RSS_090  = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10   = 'http://purl.org/rss/1.0/';

    /**
     * Feed type constants
     */
    const TYPE_ANY              = 'any';
    const TYPE_ATOM_03          = 'atom-03';
    const TYPE_ATOM_10          = 'atom-10';
    const TYPE_ATOM_10_ENTRY    = 'atom-10-entry';
    const TYPE_ATOM_ANY         = 'atom';
    const TYPE_RSS_090          = 'rss-090';
    const TYPE_RSS_091          = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092          = 'rss-092';
    const TYPE_RSS_093          = 'rss-093';
    const TYPE_RSS_094          = 'rss-094';
    const TYPE_RSS_10           = 'rss-10';
    const TYPE_RSS_20           = 'rss-20';
    const TYPE_RSS_ANY          = 'rss';

    /**
     * Cache instance
     *
     * @var CacheStorage
     */
    protected static $cache = null;

    /**
     * HTTP client object to use for retrieving feeds
     *
     * @var ZendHttp\Client
     */
    protected static $httpClient = null;

    /**
     * Override HTTP PUT and DELETE request methods?
     *
     * @var bool
     */
    protected static $httpMethodOverride = false;

    protected static $httpConditionalGet = false;

    protected static $extensionManager = null;

    protected static $extensions = array(
        'feed' => array(
            'DublinCore\Feed',
            'Atom\Feed'
        ),
        'entry' => array(
            'Content\Entry',
            'DublinCore\Entry',
            'Atom\Entry'
        ),
        'core' => array(
            'DublinCore\Feed',
            'Atom\Feed',
            'Content\Entry',
            'DublinCore\Entry',
            'Atom\Entry'
        )
    );

    /**
     * Get the Feed cache
     *
     * @return CacheStorage
     */
    public static function getCache()
    {
        return static::$cache;
    }

    /**
     * Set the feed cache
     *
     * @param  CacheStorage $cache
     * @return void
     */
    public static function setCache(CacheStorage $cache)
    {
        static::$cache = $cache;
    }

    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feeds.
     *
     * @param  ZendHttp\Client $httpClient
     * @return void
     */
    public static function setHttpClient(ZendHttp\Client $httpClient)
    {
        static::$httpClient = $httpClient;
    }


    /**
     * Gets the HTTP client object. If none is set, a new ZendHttp\Client will be used.
     *
     * @return ZendHttp\Client
     */
    public static function getHttpClient()
    {
        if (!static::$httpClient instanceof ZendHttp\Client) {
            static::$httpClient = new ZendHttp\Client();
        }

        return static::$httpClient;
    }

    /**
     * Toggle using POST instead of PUT and DELETE HTTP methods
     *
     * Some feed implementations do not accept PUT and DELETE HTTP
     * methods, or they can't be used because of proxies or other
     * measures. This allows turning on using POST where PUT and
     * DELETE would normally be used; in addition, an
     * X-Method-Override header will be sent with a value of PUT or
     * DELETE as appropriate.
     *
     * @param  bool $override Whether to override PUT and DELETE.
     * @return void
     */
    public static function setHttpMethodOverride($override = true)
    {
        static::$httpMethodOverride = $override;
    }

    /**
     * Get the HTTP override state
     *
     * @return bool
     */
    public static function getHttpMethodOverride()
    {
        return static::$httpMethodOverride;
    }

    /**
     * Set the flag indicating whether or not to use HTTP conditional GET
     *
     * @param  bool $bool
     * @return void
     */
    public static function useHttpConditionalGet($bool = true)
    {
        static::$httpConditionalGet = $bool;
    }

    /**
     * Import a feed by providing a URI
     *
     * @param  string $uri The URI to the feed
     * @param  string $etag OPTIONAL Last received ETag for this resource
     * @param  string $lastModified OPTIONAL Last-Modified value for this resource
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public static function import($uri, $etag = null, $lastModified = null)
    {
        $cache       = self::getCache();
        $feed        = null;
        $responseXml = '';
        $client      = self::getHttpClient();
        $client->resetParameters();
        $headers = new ZendHttp\Headers();
        $client->setHeaders($headers);
        $client->setUri($uri);
        $cacheId = 'Zend_Feed_Reader_' . md5($uri);

        if (static::$httpConditionalGet && $cache) {
            $data = $cache->getItem($cacheId);
            if ($data) {
                if ($etag === null) {
                    $etag = $cache->getItem($cacheId . '_etag');
                }
                if ($lastModified === null) {
                    $lastModified = $cache->getItem($cacheId . '_lastmodified');
                }
                if ($etag) {
                    $headers->addHeaderLine('If-None-Match', $etag);
                }
                if ($lastModified) {
                    $headers->addHeaderLine('If-Modified-Since', $lastModified);
                }
            }
            $response = $client->send();
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 304) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            if ($response->getStatusCode() == 304) {
                $responseXml = $data;
            } else {
                $responseXml = $response->getBody();
                $cache->setItem($cacheId, $responseXml);
                if ($response->getHeaders()->get('ETag')) {
                    $cache->setItem($cacheId . '_etag', $response->getHeaders()->get('ETag')->getFieldValue());
                }
                if ($response->getHeaders()->get('Last-Modified')) {
                    $cache->setItem($cacheId . '_lastmodified', $response->getHeaders()->get('Last-Modified')->getFieldValue());
                }
            }
            return static::importString($responseXml);
        } elseif ($cache) {
            $data = $cache->getItem($cacheId);
            if ($data) {
                return static::importString($data);
            }
            $response = $client->send();
            if ((int) $response->getStatusCode() !== 200) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            $responseXml = $response->getBody();
            $cache->setItem($cacheId, $responseXml);
            return static::importString($responseXml);
        } else {
            $response = $client->send();
            if ((int) $response->getStatusCode() !== 200) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            $reader = static::importString($response->getBody());
            $reader->setOriginalSourceUri($uri);
            return $reader;
        }
    }

    /**
     * Import a feed from a remote URI
     *
     * Performs similarly to import(), except it uses the HTTP client passed to
     * the method, and does not take into account cached data.
     *
     * Primary purpose is to make it possible to use the Reader with alternate
     * HTTP client implementations.
     *
     * @param  string $uri
     * @param  Http\Client $client
     * @return self
     * @throws Exception\RuntimeException if response is not an Http\ResponseInterface
     */
    public static function importRemoteFeed($uri, Http\ClientInterface $client)
    {
        $response = $client->get($uri);
        if (!$response instanceof Http\ResponseInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Did not receive a %s\Http\ResponseInterface from the provided HTTP client; received "%s"',
                __NAMESPACE__,
                (is_object($response) ? get_class($response) : gettype($response))
            ));
        }

        if ((int) $response->getStatusCode() !== 200) {
            throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
        }
        $reader = static::importString($response->getBody());
        $reader->setOriginalSourceUri($uri);
        return $reader;
    }

    /**
     * Import a feed from a string
     *
     * @param  string $string
     * @return Feed\FeedInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function importString($string)
    {
        $libxmlErrflag = libxml_use_internal_errors(true);
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new DOMDocument;
        $status = $dom->loadXML(trim($string));
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new Exception\InvalidArgumentException(
                    'Invalid XML: Detected use of illegal DOCTYPE'
                );
            }
        }
        libxml_disable_entity_loader($oldValue);
        libxml_use_internal_errors($libxmlErrflag);

        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse XML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse XML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }

        $type = static::detectType($dom);

        static::registerCoreExtensions();

        if (substr($type, 0, 3) == 'rss') {
            $reader = new Feed\Rss($dom, $type);
        } elseif (substr($type, 8, 5) == 'entry') {
            $reader = new Entry\Atom($dom->documentElement, 0, self::TYPE_ATOM_10);
        } elseif (substr($type, 0, 4) == 'atom') {
            $reader = new Feed\Atom($dom, $type);
        } else {
            throw new Exception\RuntimeException('The URI used does not point to a '
            . 'valid Atom, RSS or RDF feed that Zend\Feed\Reader can parse.');
        }
        return $reader;
    }

    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @throws Exception\RuntimeException
     * @return Feed\FeedInterface
     */
    public static function importFile($filename)
    {
        ErrorHandler::start();
        $feed = file_get_contents($filename);
        $err  = ErrorHandler::stop();
        if ($feed === false) {
            throw new Exception\RuntimeException("File '{$filename}' could not be loaded", 0, $err);
        }
        return static::importString($feed);
    }

    /**
     * Find feed links
     *
     * @param $uri
     * @return FeedSet
     * @throws Exception\RuntimeException
     */
    public static function findFeedLinks($uri)
    {
        $client = static::getHttpClient();
        $client->setUri($uri);
        $response = $client->send();
        if ($response->getStatusCode() !== 200) {
            throw new Exception\RuntimeException("Failed to access $uri, got response code " . $response->getStatusCode());
        }
        $responseHtml = $response->getBody();
        $libxmlErrflag = libxml_use_internal_errors(true);
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new DOMDocument;
        $status = $dom->loadHTML(trim($responseHtml));
        libxml_disable_entity_loader($oldValue);
        libxml_use_internal_errors($libxmlErrflag);
        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse HTML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse HTML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }
        $feedSet = new FeedSet;
        $links = $dom->getElementsByTagName('link');
        $feedSet->addLinks($links, $uri);
        return $feedSet;
    }

    /**
     * Detect the feed type of the provided feed
     *
     * @param  Feed\AbstractFeed|DOMDocument|string $feed
     * @param  bool $specOnly
     * @return string
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function detectType($feed, $specOnly = false)
    {
        if ($feed instanceof Feed\AbstractFeed) {
            $dom = $feed->getDomDocument();
        } elseif ($feed instanceof DOMDocument) {
            $dom = $feed;
        } elseif (is_string($feed) && !empty($feed)) {
            ErrorHandler::start(E_NOTICE|E_WARNING);
            ini_set('track_errors', 1);
            $oldValue = libxml_disable_entity_loader(true);
            $dom = new DOMDocument;
            $status = $dom->loadXML($feed);
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\InvalidArgumentException(
                        'Invalid XML: Detected use of illegal DOCTYPE'
                    );
                }
            }
            libxml_disable_entity_loader($oldValue);
            ini_restore('track_errors');
            ErrorHandler::stop();
            if (!$status) {
                if (!isset($phpErrormsg)) {
                    if (function_exists('xdebug_is_enabled')) {
                        $phpErrormsg = '(error message not available, when XDebug is running)';
                    } else {
                        $phpErrormsg = '(error message not available)';
                    }
                }
                throw new Exception\RuntimeException("DOMDocument cannot parse XML: $phpErrormsg");
            }
        } else {
            throw new Exception\InvalidArgumentException('Invalid object/scalar provided: must'
            . ' be of type Zend\Feed\Reader\Feed, DomDocument or string');
        }
        $xpath = new DOMXPath($dom);

        if ($xpath->query('/rss')->length) {
            $type = self::TYPE_RSS_ANY;
            $version = $xpath->evaluate('string(/rss/@version)');

            if (strlen($version) > 0) {
                switch ($version) {
                    case '2.0':
                        $type = self::TYPE_RSS_20;
                        break;

                    case '0.94':
                        $type = self::TYPE_RSS_094;
                        break;

                    case '0.93':
                        $type = self::TYPE_RSS_093;
                        break;

                    case '0.92':
                        $type = self::TYPE_RSS_092;
                        break;

                    case '0.91':
                        $type = self::TYPE_RSS_091;
                        break;
                }
            }

            return $type;
        }

        $xpath->registerNamespace('rdf', self::NAMESPACE_RDF);

        if ($xpath->query('/rdf:RDF')->length) {
            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_10);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_10;
            }

            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_090);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_090;
            }
        }

        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_10);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_10;
        }

        if ($xpath->query('//atom:entry')->length) {
            if ($specOnly == true) {
                return self::TYPE_ATOM_10;
            } else {
                return self::TYPE_ATOM_10_ENTRY;
            }
        }

        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_03);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_03;
        }

        return self::TYPE_ANY;
    }

    /**
     * Set plugin manager for use with Extensions
     *
     * @param ExtensionManagerInterface $extensionManager
     */
    public static function setExtensionManager(ExtensionManagerInterface $extensionManager)
    {
        static::$extensionManager = $extensionManager;
    }

    /**
     * Get plugin manager for use with Extensions
     *
     * @return ExtensionManagerInterface
     */
    public static function getExtensionManager()
    {
        if (!isset(static::$extensionManager)) {
            static::setExtensionManager(new ExtensionManager());
        }
        return static::$extensionManager;
    }

    /**
     * Register an Extension by name
     *
     * @param  string $name
     * @return void
     * @throws Exception\RuntimeException if unable to resolve Extension class
     */
    public static function registerExtension($name)
    {
        $feedName  = $name . '\Feed';
        $entryName = $name . '\Entry';
        $manager   = static::getExtensionManager();
        if (static::isRegistered($name)) {
            if ($manager->has($feedName) || $manager->has($entryName)) {
                return;
            }
        }

        if (!$manager->has($feedName) && !$manager->has($entryName)) {
            throw new Exception\RuntimeException('Could not load extension: ' . $name
                . ' using Plugin Loader. Check prefix paths are configured and extension exists.');
        }
        if ($manager->has($feedName)) {
            static::$extensions['feed'][] = $feedName;
        }
        if ($manager->has($entryName)) {
            static::$extensions['entry'][] = $entryName;
        }
    }

    /**
     * Is a given named Extension registered?
     *
     * @param  string $extensionName
     * @return bool
     */
    public static function isRegistered($extensionName)
    {
        $feedName  = $extensionName . '\Feed';
        $entryName = $extensionName . '\Entry';
        if (in_array($feedName, static::$extensions['feed'])
            || in_array($entryName, static::$extensions['entry'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get a list of extensions
     *
     * @return array
     */
    public static function getExtensions()
    {
        return static::$extensions;
    }

    /**
     * Reset class state to defaults
     *
     * @return void
     */
    public static function reset()
    {
        static::$cache              = null;
        static::$httpClient         = null;
        static::$httpMethodOverride = false;
        static::$httpConditionalGet = false;
        static::$extensionManager   = null;
        static::$extensions         = array(
            'feed' => array(
                'DublinCore\Feed',
                'Atom\Feed'
            ),
            'entry' => array(
                'Content\Entry',
                'DublinCore\Entry',
                'Atom\Entry'
            ),
            'core' => array(
                'DublinCore\Feed',
                'Atom\Feed',
                'Content\Entry',
                'DublinCore\Entry',
                'Atom\Entry'
            )
        );
    }

    /**
     * Register core (default) extensions
     *
     * @return void
     */
    protected static function registerCoreExtensions()
    {
        static::registerExtension('DublinCore');
        static::registerExtension('Content');
        static::registerExtension('Atom');
        static::registerExtension('Slash');
        static::registerExtension('WellFormedWeb');
        static::registerExtension('Thread');
        static::registerExtension('Podcast');
    }

    /**
     * Utility method to apply array_unique operation to a multidimensional
     * array.
     *
     * @param array
     * @return array
     */
    public static function arrayUnique(array $array)
    {
        foreach ($array as &$value) {
            $value = serialize($value);
        }
        $array = array_unique($array);
        foreach ($array as &$value) {
            $value = unserialize($value);
        }
        return $array;
    }
}
