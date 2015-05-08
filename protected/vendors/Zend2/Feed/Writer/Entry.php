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
use Zend\Feed\Writer\Exception;

/**
*/
class Entry
{

    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Registered extensions
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $type = null;

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
     * @return Entry
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
     * @return Entry
     */
    public function addAuthors(array $authors)
    {
        foreach ($authors as $author) {
            $this->addAuthor($author);
        }

        return $this;
    }

    /**
     * Set the feed character encoding
     *
     * @param string $encoding
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Set the copyright entry
     *
     * @param string $copyright
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Set the entry's content
     *
     * @param string $content
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setContent($content)
    {
        if (empty($content) || !is_string($content)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['content'] = $content;

        return $this;
    }

    /**
     * Set the feed creation date
     *
     * @param null|int|DateTime $date
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setDateCreated($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp passed as parameter');
        }
        $this->data['dateCreated'] = $date;

        return $this;
    }

    /**
     * Set the feed modification date
     *
     * @param null|int|DateTime $date
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setDateModified($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp passed as parameter');
        }
        $this->data['dateModified'] = $date;

        return $this;
    }

    /**
     * Set the feed description
     *
     * @param string $description
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Set the feed ID
     *
     * @param string $id
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setId($id)
    {
        if (empty($id) || !is_string($id)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->data['id'] = $id;

        return $this;
    }

    /**
     * Set a link to the HTML source of this entry
     *
     * @param string $link
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Set the number of comments associated with this entry
     *
     * @param int $count
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setCommentCount($count)
    {
        if (!is_numeric($count) || (int) $count != $count || (int) $count < 0) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "count" must be a positive integer number or zero');
        }
        $this->data['commentCount'] = (int) $count;

        return $this;
    }

    /**
     * Set a link to a HTML page containing comments associated with this entry
     *
     * @param string $link
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setCommentLink($link)
    {
        if (empty($link) || !is_string($link) || !Uri::factory($link)->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        $this->data['commentLink'] = $link;

        return $this;
    }

    /**
     * Set a link to an XML feed for any comments associated with this entry
     *
     * @param array $link
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setCommentFeedLink(array $link)
    {
        if (!isset($link['uri']) || !is_string($link['uri']) || !Uri::factory($link['uri'])->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        if (!isset($link['type']) || !in_array($link['type'], array('atom', 'rss', 'rdf'))) {
            throw new Exception\InvalidArgumentException('Invalid parameter: "type" must be one'
            . ' of "atom", "rss" or "rdf"');
        }
        if (!isset($this->data['commentFeedLinks'])) {
            $this->data['commentFeedLinks'] = array();
        }
        $this->data['commentFeedLinks'][] = $link;

        return $this;
    }

    /**
     * Set a links to an XML feed for any comments associated with this entry.
     * Each link is an array with keys "uri" and "type", where type is one of:
     * "atom", "rss" or "rdf".
     *
     * @param array $links
     * @return Entry
     */
    public function setCommentFeedLinks(array $links)
    {
        foreach ($links as $link) {
            $this->setCommentFeedLink($link);
        }

        return $this;
    }

    /**
     * Set the feed title
     *
     * @param string $title
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (!array_key_exists('content', $this->data)) {
            return null;
        }
        return $this->data['content'];
    }

    /**
     * Get the entry copyright information
     *
     * @return string
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->data)) {
            return null;
        }
        return $this->data['copyright'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->data)) {
            return null;
        }
        return $this->data['dateCreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->data)) {
            return null;
        }
        return $this->data['dateModified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->data)) {
            return null;
        }
        return $this->data['description'];
    }

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->data)) {
            return null;
        }
        return $this->data['id'];
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
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (!array_key_exists('links', $this->data)) {
            return null;
        }
        return $this->data['links'];
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->data)) {
            return null;
        }
        return $this->data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return int
     */
    public function getCommentCount()
    {
        if (!array_key_exists('commentCount', $this->data)) {
            return null;
        }
        return $this->data['commentCount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (!array_key_exists('commentLink', $this->data)) {
            return null;
        }
        return $this->data['commentLink'];
    }

    /**
     * Returns an array of URIs pointing to a feed of all comments for this entry
     * where the array keys indicate the feed type (atom, rss or rdf).
     *
     * @return string
     */
    public function getCommentFeedLinks()
    {
        if (!array_key_exists('commentFeedLinks', $this->data)) {
            return null;
        }
        return $this->data['commentFeedLinks'];
    }

    /**
     * Add a entry category
     *
     * @param array $category
     * @throws Exception\InvalidArgumentException
     * @return Entry
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
     * Set an array of entry categories
     *
     * @param array $categories
     * @return Entry
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Get the entry categories
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
     * Adds an enclosure to the entry. The array parameter may contain the
     * keys 'uri', 'type' and 'length'. Only 'uri' is required for Atom, though the
     * others must also be provided or RSS rendering (where they are required)
     * will throw an Exception.
     *
     * @param array $enclosure
     * @throws Exception\InvalidArgumentException
     * @return Entry
     */
    public function setEnclosure(array $enclosure)
    {
        if (!isset($enclosure['uri'])) {
            throw new Exception\InvalidArgumentException('Enclosure "uri" is not set');
        }
        if (!Uri::factory($enclosure['uri'])->isValid()) {
            throw new Exception\InvalidArgumentException('Enclosure "uri" is not a valid URI/IRI');
        }
        $this->data['enclosure'] = $enclosure;

        return $this;
    }

    /**
     * Retrieve an array of all enclosures to be added to entry.
     *
     * @return array
     */
    public function getEnclosure()
    {
        if (!array_key_exists('enclosure', $this->data)) {
            return null;
        }
        return $this->data['enclosure'];
    }

    /**
     * Unset a specific data point
     *
     * @param string $name
     * @return Entry
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        return $this;
    }

    /**
     * Get registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     *
     * @param string $name
     * @return object
     */
    public function getExtension($name)
    {
        if (array_key_exists($name . '\\Entry', $this->extensions)) {
            return $this->extensions[$name . '\\Entry'];
        }
        return null;
    }

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     * @return Entry
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
            } catch (\BadMethodCallException $e) {
            }
        }
        throw new Exception\BadMethodCallException('Method: ' . $method
            . ' does not exist and could not be located on a registered Extension');
    }

    /**
     * Creates a new Zend\Feed\Writer\Source data container for use. This is NOT
     * added to the current feed automatically, but is necessary to create a
     * container with some initial values preset based on the current feed data.
     *
     * @return Source
     */
    public function createSource()
    {
        $source = new Source;
        if ($this->getEncoding()) {
            $source->setEncoding($this->getEncoding());
        }
        $source->setType($this->getType());
        return $source;
    }

    /**
     * Appends a Zend\Feed\Writer\Entry object representing a new entry/item
     * the feed data container's internal group of entries.
     *
     * @param Source $source
     * @return Entry
     */
    public function setSource(Source $source)
    {
        $this->data['source'] = $source;
        return $this;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        if (isset($this->data['source'])) {
            return $this->data['source'];
        }
        return null;
    }

    /**
     * Load extensions from Zend\Feed\Writer\Writer
     *
     * @return void
     */
    protected function _loadExtensions()
    {
        $all     = Writer::getExtensions();
        $manager = Writer::getExtensionManager();
        $exts    = $all['entry'];
        foreach ($exts as $ext) {
            $this->extensions[$ext] = $manager->get($ext);
            $this->extensions[$ext]->setEncoding($this->getEncoding());
        }
    }
}
