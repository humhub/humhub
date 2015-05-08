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
use Zend\Validator;

class Atom extends Renderer\AbstractRenderer implements Renderer\RendererInterface
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
     * Render atom entry
     *
     * @return Atom
     */
    public function render()
    {
        $this->dom = new DOMDocument('1.0', $this->container->getEncoding());
        $this->dom->formatOutput = true;
        $entry = $this->dom->createElementNS(Writer\Writer::NAMESPACE_ATOM_10, 'entry');
        $this->dom->appendChild($entry);

        $this->_setSource($this->dom, $entry);
        $this->_setTitle($this->dom, $entry);
        $this->_setDescription($this->dom, $entry);
        $this->_setDateCreated($this->dom, $entry);
        $this->_setDateModified($this->dom, $entry);
        $this->_setLink($this->dom, $entry);
        $this->_setId($this->dom, $entry);
        $this->_setAuthors($this->dom, $entry);
        $this->_setEnclosure($this->dom, $entry);
        $this->_setContent($this->dom, $entry);
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
        if (!$this->getDataContainer()->getTitle()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one'
            . ' atom:title element but a title has not been set';
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
        $title->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection($this->getDataContainer()->getTitle());
        $title->appendChild($cdata);
    }

    /**
     * Set entry description
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDescription()) {
            return; // unless src content or base64
        }
        $subtitle = $dom->createElement('summary');
        $root->appendChild($subtitle);
        $subtitle->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection(
            $this->getDataContainer()->getDescription()
        );
        $subtitle->appendChild($cdata);
    }

    /**
     * Set date entry was modified
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateModified()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one'
            . ' atom:updated element but a modification date has not been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }

        $updated = $dom->createElement('updated');
        $root->appendChild($updated);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateModified()->format(DateTime::ISO8601)
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
        $el = $dom->createElement('published');
        $root->appendChild($el);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateCreated()->format(DateTime::ISO8601)
        );
        $el->appendChild($text);
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
            /**
             * This will actually trigger an Exception at the feed level if
             * a feed level author is not set.
             */
            return;
        }
        foreach ($authors as $data) {
            $author = $this->dom->createElement('author');
            $name = $this->dom->createElement('name');
            $author->appendChild($name);
            $root->appendChild($author);
            $text = $dom->createTextNode($data['name']);
            $name->appendChild($text);
            if (array_key_exists('email', $data)) {
                $email = $this->dom->createElement('email');
                $author->appendChild($email);
                $text = $dom->createTextNode($data['email']);
                $email->appendChild($text);
            }
            if (array_key_exists('uri', $data)) {
                $uri = $this->dom->createElement('uri');
                $author->appendChild($uri);
                $text = $dom->createTextNode($data['uri']);
                $uri->appendChild($text);
            }
        }
    }

    /**
     * Set entry enclosure
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setEnclosure(DOMDocument $dom, DOMElement $root)
    {
        $data = $this->container->getEnclosure();
        if ((!$data || empty($data))) {
            return;
        }
        $enclosure = $this->dom->createElement('link');
        $enclosure->setAttribute('rel', 'enclosure');
        if (isset($data['type'])) {
            $enclosure->setAttribute('type', $data['type']);
        }
        if (isset($data['length'])) {
            $enclosure->setAttribute('length', $data['length']);
        }
        $enclosure->setAttribute('href', $data['uri']);
        $root->appendChild($enclosure);
    }

    protected function _setLink(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getLink()) {
            return;
        }
        $link = $dom->createElement('link');
        $root->appendChild($link);
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->getDataContainer()->getLink());
    }

    /**
     * Set entry identifier
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setId(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getId()
        && !$this->getDataContainer()->getLink()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one '
            . 'atom:id element, or as an alternative, we can use the same '
            . 'value as atom:link however neither a suitable link nor an '
            . 'id have been set';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }

        if (!$this->getDataContainer()->getId()) {
            $this->getDataContainer()->setId(
                $this->getDataContainer()->getLink());
        }
        if (!Uri::factory($this->getDataContainer()->getId())->isValid()
            && !preg_match(
                "#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#",
                $this->getDataContainer()->getId())
            && !$this->_validateTagUri($this->getDataContainer()->getId())
        ) {
            throw new Writer\Exception\InvalidArgumentException('Atom 1.0 IDs must be a valid URI/IRI');
        }
        $id = $dom->createElement('id');
        $root->appendChild($id);
        $text = $dom->createTextNode($this->getDataContainer()->getId());
        $id->appendChild($text);
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
     * Set entry content
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     * @throws Writer\Exception\InvalidArgumentException
     */
    protected function _setContent(DOMDocument $dom, DOMElement $root)
    {
        $content = $this->getDataContainer()->getContent();
        if (!$content && !$this->getDataContainer()->getLink()) {
            $message = 'Atom 1.0 entry elements MUST contain exactly one '
            . 'atom:content element, or as an alternative, at least one link '
            . 'with a rel attribute of "alternate" to indicate an alternate '
            . 'method to consume the content.';
            $exception = new Writer\Exception\InvalidArgumentException($message);
            if (!$this->ignoreExceptions) {
                throw $exception;
            } else {
                $this->exceptions[] = $exception;
                return;
            }
        }
        if (!$content) {
            return;
        }
        $element = $dom->createElement('content');
        $element->setAttribute('type', 'xhtml');
        $xhtmlElement = $this->_loadXhtml($content);
        $xhtml = $dom->importNode($xhtmlElement, true);
        $element->appendChild($xhtml);
        $root->appendChild($element);
    }

    /**
     * Load a HTML string and attempt to normalise to XML
     */
    protected function _loadXhtml($content)
    {
        $xhtml = '';
        if (class_exists('tidy', false)) {
            $tidy = new \tidy;
            $config = array(
                'output-xhtml' => true,
                'show-body-only' => true,
                'quote-nbsp' => false
            );
            $encoding = str_replace('-', '', $this->getEncoding());
            $tidy->parseString($content, $config, $encoding);
            $tidy->cleanRepair();
            $xhtml = (string) $tidy;
        } else {
            $xhtml = $content;
        }
        $xhtml = preg_replace(array(
            "/(<[\/]?)([a-zA-Z]+)/"
        ), '$1xhtml:$2', $xhtml);
        $dom = new DOMDocument('1.0', $this->getEncoding());
        $dom->loadXML('<xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml">'
            . $xhtml . '</xhtml:div>');
        return $dom->documentElement;
    }

    /**
     * Set entry categories
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
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
            $category->setAttribute('term', $cat['term']);
            if (isset($cat['label'])) {
                $category->setAttribute('label', $cat['label']);
            } else {
                $category->setAttribute('label', $cat['term']);
            }
            if (isset($cat['scheme'])) {
                $category->setAttribute('scheme', $cat['scheme']);
            }
            $root->appendChild($category);
        }
    }

    /**
     * Append Source element (Atom 1.0 Feed Metadata)
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setSource(DOMDocument $dom, DOMElement $root)
    {
        $source = $this->getDataContainer()->getSource();
        if (!$source) {
            return;
        }
        $renderer = new Renderer\Feed\AtomSource($source);
        $renderer->setType($this->getType());
        $element = $renderer->render()->getElement();
        $imported = $dom->importNode($element, true);
        $root->appendChild($imported);
    }
}
