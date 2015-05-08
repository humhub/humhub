<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer\Renderer\Entry;

use DateTime;
use DOMDocument;
use DOMElement;
use Zend\Feed\Uri;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Renderer;

/**
*/
class Rss extends Renderer\AbstractRenderer implements Renderer\RendererInterface
{
    /**
     * Constructor
     *
     * @param  Writer\Entry $container
     */
    public function __construct(Writer\Entry $container)
    {
        parent::__construct($container);
    }

    /**
     * Render RSS entry
     *
     * @return Rss
     */
    public function render()
    {
        $this->dom = new DOMDocument('1.0', $this->container->getEncoding());
        $this->dom->formatOutput = true;
        $this->dom->substituteEntities = false;
        $entry = $this->dom->createElement('item');
        $this->dom->appendChild($entry);

        $this->_setTitle($this->dom, $entry);
        $this->_setDescription($this->dom, $entry);
        $this->_setDateCreated($this->dom, $entry);
        $this->_setDateModified($this->dom, $entry);
        $this->_setLink($this->dom, $entry);
        $this->_setId($this->dom, $entry);
        $this->_setAuthors($this->dom, $entry);
        $this->_setEnclosure($this->dom, $entry);
        $this->_setCommentLink($this->dom, $entry);
        $this->_setCategories($this->dom, $entry);
        foreach ($this->extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDOMDocument($this->getDOMDocument(), $entry);
            $ext->render();
        }

        return $this;
    }

    /**
     * Set entry title
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setTitle(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDescription()
        && !$this->getDataContainer()->getTitle()) {
            $message = 'RSS 2.0 entry elements SHOULD contain exactly one'
            . ' title element but a title has not been set. In addition, there'
            . ' is no description as required in the absence of a title.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        $title = $dom->createElement('title');
        $root->appendChild($title);
        $text = $dom->createTextNode($this->getDataContainer()->getTitle());
        $title->appendChild($text);
    }

    /**
     * Set entry description
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDescription()
        && !$this->getDataContainer()->getTitle()) {
            $message = 'RSS 2.0 entry elements SHOULD contain exactly one'
            . ' description element but a description has not been set. In'
            . ' addition, there is no title element as required in the absence'
            . ' of a description.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        if (!$this->getDataContainer()->getDescription()) {
            return;
        }
        $subtitle = $dom->createElement('description');
        $root->appendChild($subtitle);
        $text = $dom->createCDATASection($this->getDataContainer()->getDescription());
        $subtitle->appendChild($text);
    }

    /**
     * Set date entry was last modified
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateModified()) {
            return;
        }

        $updated = $dom->createElement('pubDate');
        $root->appendChild($updated);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateModified()->format(DateTime::RSS)
        );
        $updated->appendChild($text);
    }

    /**
     * Set date entry was created
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setDateCreated(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateCreated()) {
            return;
        }
        if (!$this->getDataContainer()->getDateModified()) {
            $this->getDataContainer()->setDateModified(
                $this->getDataContainer()->getDateCreated()
            );
        }
    }

    /**
     * Set entry authors
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        $authors = $this->container->getAuthors();
        if ((!$authors || empty($authors))) {
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->createElement('author');
            $name = $data['name'];
            if (array_key_exists('email', $data)) {
                $name = $data['email'] . ' (' . $data['name'] . ')';
            }
            $text = $dom->createTextNode($name);
            $author->appendChild($text);
            $root->appendChild($author);
        }
    }

    /**
     * Set entry enclosure
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setEnclosure(DOMDocument $dom, DOMElement $root)
    {
        $data = $this->container->getEnclosure();
        if ((!$data || empty($data))) {
            return;
        }
        if (!isset($data['type'])) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "type" is not set');
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        if (!isset($data['length'])) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "length" is not set');
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        if (isset($data['length']) && (int) $data['length'] <= 0) {
            $exception = new Writer\Exception\InvalidArgumentException('Enclosure "length" must be an integer'
            . ' indicating the content\'s length in bytes');
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        $enclosure = $this->dom->createElement('enclosure');
        $enclosure->setAttribute('type', $data['type']);
        $enclosure->setAttribute('length', $data['length']);
        $enclosure->setAttribute('url', $data['uri']);
        $root->appendChild($enclosure);
    }

    /**
     * Set link to entry
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setLink(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getLink()) {
            return;
        }
        $link = $dom->createElement('link');
        $root->appendChild($link);
        $text = $dom->createTextNode($this->getDataContainer()->getLink());
        $link->appendChild($text);
    }

    /**
     * Set entry identifier
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setId(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getId()
        && !$this->getDataContainer()->getLink()) {
            return;
        }

        $id = $dom->createElement('guid');
        $root->appendChild($id);
        if (!$this->getDataContainer()->getId()) {
            $this->getDataContainer()->setId(
                $this->getDataContainer()->getLink());
        }
        $text = $dom->createTextNode($this->getDataContainer()->getId());
        $id->appendChild($text);
        if (!Uri::factory($this->getDataContainer()->getId())->isValid()) {
            $id->setAttribute('isPermaLink', 'false');
        }
    }

    /**
     * Set link to entry comments
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCommentLink(DOMDocument $dom, DOMElement $root)
    {
        $link = $this->getDataContainer()->getCommentLink();
        if (!$link) {
            return;
        }
        $clink = $this->dom->createElement('comments');
        $text = $dom->createTextNode($link);
        $clink->appendChild($text);
        $root->appendChild($clink);
    }

    /**
     * Set entry categories
     *
     * @param DOMDocument $dom
     * @param DOMElement $root
     * @return void
     */
    protected function _setCategories(DOMDocument $dom, DOMElement $root)
    {
        $categories = $this->getDataContainer()->getCategories();
        if (!$categories) {
            return;
        }
        foreach ($categories as $cat) {
            $category = $dom->createElement('category');
            if (isset($cat['scheme'])) {
                $category->setAttribute('domain', $cat['scheme']);
            }
            $text = $dom->createCDATASection($cat['term']);
            $category->appendChild($text);
            $root->appendChild($category);
        }
    }
}
