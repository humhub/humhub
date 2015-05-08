<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer\Renderer\Feed;

use DOMDocument;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Renderer;

/**
*/
class Atom extends AbstractAtom implements Renderer\RendererInterface
{
    /**
     * Constructor
     *
     * @param  Writer\Feed $container
     */
    public function __construct(Writer\Feed $container)
    {
        parent::__construct($container);
    }

    /**
     * Render Atom feed
     *
     * @return Atom
     */
    public function render()
    {
        if (!$this->container->getEncoding()) {
            $this->container->setEncoding('UTF-8');
        }
        $this->dom = new DOMDocument('1.0', $this->container->getEncoding());
        $this->dom->formatOutput = true;
        $root = $this->dom->createElementNS(
            Writer\Writer::NAMESPACE_ATOM_10, 'feed'
        );
        $this->setRootElement($root);
        $this->dom->appendChild($root);
        $this->_setLanguage($this->dom, $root);
        $this->_setBaseUrl($this->dom, $root);
        $this->_setTitle($this->dom, $root);
        $this->_setDescription($this->dom, $root);
        $this->_setImage($this->dom, $root);
        $this->_setDateCreated($this->dom, $root);
        $this->_setDateModified($this->dom, $root);
        $this->_setGenerator($this->dom, $root);
        $this->_setLink($this->dom, $root);
        $this->_setFeedLinks($this->dom, $root);
        $this->_setId($this->dom, $root);
        $this->_setAuthors($this->dom, $root);
        $this->_setCopyright($this->dom, $root);
        $this->_setCategories($this->dom, $root);
        $this->_setHubs($this->dom, $root);

        foreach ($this->extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDOMDocument($this->getDOMDocument(), $root);
            $ext->render();
        }

        foreach ($this->container as $entry) {
            if ($this->getDataContainer()->getEncoding()) {
                $entry->setEncoding($this->getDataContainer()->getEncoding());
            }
            if ($entry instanceof Writer\Entry) {
                $renderer = new Renderer\Entry\Atom($entry);
            } else {
                if (!$this->dom->documentElement->hasAttribute('xmlns:at')) {
                    $this->dom->documentElement->setAttribute(
                        'xmlns:at', 'http://purl.org/atompub/tombstones/1.0'
                    );
                }
                $renderer = new Renderer\Entry\AtomDeleted($entry);
            }
            if ($this->ignoreExceptions === true) {
                $renderer->ignoreExceptions();
            }
            $renderer->setType($this->getType());
            $renderer->setRootElement($this->dom->documentElement);
            $renderer->render();
            $element = $renderer->getElement();
            $imported = $this->dom->importNode($element, true);
            $root->appendChild($imported);
        }
        return $this;
    }
}
