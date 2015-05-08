<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer\Renderer\Entry\Atom;

use DateTime;
use DOMDocument;
use DOMElement;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Renderer;

class Deleted extends Renderer\AbstractRenderer implements Renderer\RendererInterface
{
    /**
     * Constructor
     *
     * @param  Writer\Deleted $container
     */
    public function __construct(Writer\Deleted $container)
    {
        parent::__construct($container);
    }

    /**
     * Render atom entry
     *
     * @return Writer\Renderer\Entry\Atom
     */
    public function render()
    {
        $this->dom = new DOMDocument('1.0', $this->container->getEncoding());
        $this->dom->formatOutput = true;
        $entry = $this->dom->createElement('at:deleted-entry');
        $this->dom->appendChild($entry);

        $entry->setAttribute('ref', $this->container->getReference());
        $entry->setAttribute('when', $this->container->getWhen()->format(DateTime::ISO8601));

        $this->_setBy($this->dom, $entry);
        $this->_setComment($this->dom, $entry);

        return $this;
    }

    /**
     * Set tombstone comment
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setComment(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getComment()) {
            return;
        }
        $c = $dom->createElement('at:comment');
        $root->appendChild($c);
        $c->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection($this->getDataContainer()->getComment());
        $c->appendChild($cdata);
    }

    /**
     * Set entry authors
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setBy(DOMDocument $dom, DOMElement $root)
    {
        $data = $this->container->getBy();
        if ((!$data || empty($data))) {
            return;
        }
        $author = $this->dom->createElement('at:by');
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
