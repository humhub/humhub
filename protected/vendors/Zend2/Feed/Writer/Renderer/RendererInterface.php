<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer\Renderer;

use DOMDocument;
use DOMElement;

/**
*/
interface RendererInterface
{
    /**
     * Render feed/entry
     *
     * @return void
     */
    public function render();

    /**
     * Save feed and/or entry to XML and return string
     *
     * @return string
     */
    public function saveXml();

    /**
     * Get DOM document
     *
     * @return DOMDocument
     */
    public function getDomDocument();

    /**
     * Get document element from DOM
     *
     * @return DOMElement
     */
    public function getElement();

    /**
     * Get data container containing feed items
     *
     * @return mixed
     */
    public function getDataContainer();

    /**
     * Should exceptions be ignored?
     *
     * @return mixed
     */
    public function ignoreExceptions();

    /**
     * Get list of thrown exceptions
     *
     * @return array
     */
    public function getExceptions();

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType();

    /**
     * Sets the absolute root element for the XML feed being generated. This
     * helps simplify the appending of namespace declarations, but also ensures
     * namespaces are added to the root element - not scattered across the entire
     * XML file - may assist namespace unsafe parsers and looks pretty ;).
     *
     * @param DOMElement $root
     */
    public function setRootElement(DOMElement $root);

    /**
     * Retrieve the absolute root element for the XML feed being generated.
     *
     * @return DOMElement
     */
    public function getRootElement();
}
