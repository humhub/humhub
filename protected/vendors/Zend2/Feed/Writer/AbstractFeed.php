<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer;

use DateTime;
use Zend\Feed\Uri;
use Zend\Validator;

class AbstractFeed
{
    /**
     * Contains all Feed level date to append in feed output
     *
     * @var array
     */
    protected $data = array();

    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type = null;

    /**
     * @var $extensions
     */
    protected $extensions;

    /**
     * Constructor: Primarily triggers the registration of core extensions and
     * loads those appropriate to this data container.
     *
     */
    public function __construct()
    {
        Writer::registerCoreExtensions();
        $this->_loadExtensions();
    }

    /**
     * Set a single author
     *
     * The following option keys are supported:
     * 'name'  => (string) The name
     * 'email' => (string) An optional email
     * 'uri'   => (string) An optional and valid URI
     *
     * @param array $author
     * @throws Exception\InvalidArgumentException If any value of $author not follow the format.
     * @return AbstractFeed
     */
    public function addAuthor(array $author)
    {
        // Check array values
        if (!array_key_exists('name', $author)
            || empty($author['name'])
            || !is_string($author['name'])
        ) {
            throw new Exception\InvalidArgumentException(
                'Invalid parameter: author array must include a "name" key with a non-empty string value');
        }

        if (isset($author['email'])) {
            if (empty($author['email']) || !is_string($author['email'])) {
                throw new Exception\InvalidArgumentException(
                    'Invalid parameter: "email" array value must be a non-empty string');
            }
        }
        if (isset($author['uri'])) {
            if (empty($author['uri']) || !is_string($author['uri']) ||
                !Uri::factory($author['uri'])->isValid()
            ) {
                throw new Exception\InvalidArgumentException(
                    'Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
            }
        }

        $this->data['authors'][] = $author;

        return $this;
    }

    /**
     * Set an array with feed authors
     *
     * @see addAuthor
     * @param array $authors
     * @return AbstractFeed
     */
    public function addAuthors(array $authors)
    {
        foreach ($authors as $author) {
            $this->addAuthor($author);
        }

        return $this;
    }

    /**
     * Set the copyright entry
     *
     * @param  string      $copyright
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setCopyright($copyright)
    {
        if (empty($copyright) || !is_string($copyright)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['copyright'] = $copyright;

        return $this;
    }

    /**
     * Set the feed creation date
     *
     * @param null|int|DateTime
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setDateCreated($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp'
                                                         . ' passed as parameter');
        }
        $this->data['dateCreated'] = $date;

        return $this;
    }

    /**
     * Set the feed modification date
     *
     * @param null|int|DateTime
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setDateModified($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp'
                                                         . ' passed as parameter');
        }
        $this->data['dateModified'] = $date;

        return $this;
    }

    /**
     * Set the feed last-build date. Ignored for Atom 1.0.
     *
     * @param null|int|DateTime
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setLastBuildDate($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp'
                                                         . ' passed as parameter');
        }
        $this->data['lastBuildDate'] = $date;

        return $this;
    }

    /**
     * Set the feed description
     *
     * @param string $description
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setDescription($description)
    {
        if (empty($description) || !is_string($description)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['description'] = $description;

        return $this;
    }

    /**
     * Set the feed generator entry
     *
     * @param array|string $name
     * @param null|string $version
     * @param null|string $uri
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setGenerator($name, $version = null, $uri = null)
    {
        if (is_array($name)) {
            $data = $name;
            if (empty($data['name']) || !is_string($data['name'])) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = array('name' => $data['name']);
            if (isset($data['version'])) {
                if (empty($data['version']) || !is_string($data['version'])) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $data['version'];
            }
            if (isset($data['uri'])) {
                if (empty($data['uri']) || !is_string($data['uri']) || !Uri::factory($data['uri'])->isValid()) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $data['uri'];
            }
        } else {
            if (empty($name) || !is_string($name)) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = array('name' => $name);
            if (isset($version)) {
                if (empty($version) || !is_string($version)) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $version;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Uri::factory($uri)->isValid()) {
                    throw new Exception\InvalidArgumentException('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $uri;
            }
        }
        $this->data['generator'] = $generator;

        return $this;
    }

    /**
     * Set the feed ID - URI or URN (via PCRE pattern) supported
     *
     * @param string $id
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setId($id)
    {
        if ((empty($id) || !is_string($id) || !Uri::factory($id)->isValid())
            && !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#", $id)
            && !$this->_validateTagUri($id)
        ) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->data['id'] = $id;

        return $this;
    }

    /**
     * Validate a URI using the tag scheme (RFC 4151)
     *
     * @param string $id
     * @return bool
     */
    protected function _validateTagUri($id)
    {
        if (preg_match('/^tag:(?P<name>.*),(?P<date>\d{4}-?\d{0,2}-?\d{0,2}):(?P<specific>.*)(.*:)*$/', $id, $matches)) {
            $dvalid = false;
            $nvalid = false;
            $date = $matches['date'];
            $d6 = strtotime($date);
            if ((strlen($date) == 4) && $date <= date('Y')) {
                $dvalid = true;
            } elseif ((strlen($date) == 7) && ($d6 < strtotime("now"))) {
                $dvalid = true;
            } elseif ((strlen($date) == 10) && ($d6 < strtotime("now"))) {
                $dvalid = true;
            }
            $validator = new Validator\EmailAddress;
            if ($validator->isValid($matches['name'])) {
                $nvalid = true;
            } else {
                $nvalid = $validator->isValid('info@' . $matches['name']);
            }
            return $dvalid && $nvalid;

        }
        return false;
    }

    /**
     * Set a feed image (URI at minimum). Parameter is a single array with the
     * required key 'uri'. When rendering as RSS, the required keys are 'uri',
     * 'title' and 'link'. RSS also specifies three optional parameters 'width',
     * 'height' and 'description'. Only 'uri' is required and used for Atom rendering.
     *
     * @param array $data
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setImage(array $data)
    {
        if (empty($data['uri']) || !is_string($data['uri'])
            || !Uri::factory($data['uri'])->isValid()
        ) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter \'uri\''
            . ' must be a non-empty string and valid URI/IRI');
        }
        $this->data['image'] = $data;

        return $this;
    }

    /**
     * Set the feed language
     *
     * @param string $language
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setLanguage($language)
    {
        if (empty($language) || !is_string($language)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['language'] = $language;

        return $this;
    }

    /**
     * Set a link to the HTML source
     *
     * @param string $link
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setLink($link)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->data['link'] = $link;

        return $this;
    }

    /**
     * Set a link to an XML feed for any feed type/version
     *
     * @param string $link
     * @param string $type
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setFeedLink($link, $type)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link"" must be a non-empty string and valid URI/IRI');
        }
        if (!in_array(strtolower($type), array('rss', 'rdf', 'atom'))) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "type"; You must declare the type of feed the link points to, i.e. RSS, RDF or Atom');
        }
        $this->data['feedLinks'][strtolower($type)] = $link;

        return $this;
    }

    /**
     * Set the feed title
     *
     * @param string $title
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setTitle($title)
    {
        if (empty($title) || !is_string($title)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['title'] = $title;

        return $this;
    }

    /**
     * Set the feed character encoding
     *
     * @param string $encoding
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['encoding'] = $encoding;

        return $this;
    }

    /**
     * Set the feed's base URL
     *
     * @param string $url
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function setBaseUrl($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "url" array value'
            . ' must be a non-empty string and valid URI/IRI');
        }
        $this->data['baseUrl'] = $url;

        return $this;
    }

    /**
     * Add a Pubsubhubbub hub endpoint URL
     *
     * @param string $url
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function addHub($url)
    {
        if (empty($url) || !is_string($url) || !Uri::factory($url)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "url" array value'
            . ' must be a non-empty string and valid URI/IRI');
        }
        if (!isset($this->data['hubs'])) {
            $this->data['hubs'] = array();
        }
        $this->data['hubs'][] = $url;

        return $this;
    }

    /**
     * Add Pubsubhubbub hub endpoint URLs
     *
     * @param array $urls
     * @return AbstractFeed
     */
    public function addHubs(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHub($url);
        }

        return $this;
    }

    /**
     * Add a feed category
     *
     * @param array $category
     * @throws Exception\InvalidArgumentException
     * @return AbstractFeed
     */
    public function addCategory(array $category)
    {
        if (!isset($category['term'])) {
            throw new Exception\InvalidArgumentException('Each category must be an array and '
            . 'contain at least a "term" element containing the machine '
            . ' readable category name');
        }
        if (isset($category['scheme'])) {
            if (empty($category['scheme'])
                || !is_string($category['scheme'])
                || !Uri::factory($category['scheme'])->isValid()
            ) {
                throw new Exception\InvalidArgumentException('The Atom scheme or RSS domain of'
                . ' a category must be a valid URI');
            }
        }
        if (!isset($this->data['categories'])) {
            $this->data['categories'] = array();
        }
        $this->data['categories'][] = $category;

        return $this;
    }

    /**
     * Set an array of feed categories
     *
     * @param array $categories
     * @return AbstractFeed
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        if (isset($this->data['authors'][$index])) {
            return $this->data['authors'][$index];
        }

        return null;
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (!array_key_exists('authors', $this->data)) {
            return null;
        }
        return $this->data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return null;
        }
        return $this->data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return null;
        }
        return $this->data['dateCreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return null;
        }
        return $this->data['dateModified'];
    }

    /**
     * Get the feed last-build date
     *
     * @return string|null
     */
    public function getLastBuildDate()
    {
        if (!array_key_exists('lastBuildDate', $this->data)) {
            return null;
        }
        return $this->data['lastBuildDate'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->data)) {
            return null;
        }
        return $this->data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (!array_key_exists('generator', $this->data)) {
            return null;
        }
        return $this->data['generator'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->data)) {
            return null;
        }
        return $this->data['id'];
    }

    /**
     * Get the feed image URI
     *
     * @return array
     */
    public function getImage()
    {
        if (!array_key_exists('image', $this->data)) {
            return null;
        }
        return $this->data['image'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (!array_key_exists('language', $this->data)) {
            return null;
        }
        return $this->data['language'];
    }

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink()
    {
        if (!array_key_exists('link', $this->data)) {
            return null;
        }
        return $this->data['link'];
    }

    /**
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLinks()
    {
        if (!array_key_exists('feedLinks', $this->data)) {
            return null;
        }
        return $this->data['feedLinks'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->data)) {
            return null;
        }
        return $this->data['title'];
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->data)) {
            return 'UTF-8';
        }
        return $this->data['encoding'];
    }

    /**
     * Get the feed's base url
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (!array_key_exists('baseUrl', $this->data)) {
            return null;
        }
        return $this->data['baseUrl'];
    }

    /**
     * Get the URLs used as Pubsubhubbub hubs endpoints
     *
     * @return string|null
     */
    public function getHubs()
    {
        if (!array_key_exists('hubs', $this->data)) {
            return null;
        }
        return $this->data['hubs'];
    }

    /**
     * Get the feed categories
     *
     * @return string|null
     */
    public function getCategories()
    {
        if (!array_key_exists('categories', $this->data)) {
            return null;
        }
        return $this->data['categories'];
    }

    /**
     * Resets the instance and deletes all data
     *
     * @return void
     */
    public function reset()
    {
        $this->data = array();
    }

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     * @return AbstractFeed
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Unset a specific data point
     *
     * @param string $name
     * @return AbstractFeed
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
        return $this;
    }

    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException if no extensions implements the method
     */
    public function __call($method, $args)
    {
        foreach ($this->extensions as $extension) {
            try {
                return call_user_func_array(array($extension, $method), $args);
            } catch (Exception\BadMethodCallException $e) {
            }
        }
        throw new Exception\BadMethodCallException(
            'Method: ' . $method . ' does not exist and could not be located on a registered Extension'
        );
    }

    /**
     * Load extensions from Zend\Feed\Writer\Writer
     *
     * @throws Exception\RuntimeException
     * @return void
     */
    protected function _loadExtensions()
    {
        $all     = Writer::getExtensions();
        $manager = Writer::getExtensionManager();
        $exts    = $all['feed'];
        foreach ($exts as $ext) {
            if (!$manager->has($ext)) {
                throw new Exception\RuntimeException(sprintf('Unable to load extension "%s"; could not resolve to class', $ext));
            }
            $this->extensions[$ext] = $manager->get($ext);
            $this->extensions[$ext]->setEncoding($this->getEncoding());
        }
    }
}
