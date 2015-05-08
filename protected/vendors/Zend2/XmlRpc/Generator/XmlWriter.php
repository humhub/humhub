<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Generator;

/**
 * XML generator adapter based on XMLWriter
 */
class XmlWriter extends AbstractGenerator
{
    /**
     * XMLWriter instance
     *
     * @var XMLWriter
     */
    protected $xmlWriter;

    /**
     * Initialized XMLWriter instance
     *
     * @return void
     */
    protected function _init()
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument('1.0', $this->encoding);
    }


    /**
     * Open a new XML element
     *
     * @param string $name XML element name
     * @return void
     */
    protected function _openElement($name)
    {
        $this->xmlWriter->startElement($name);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text XML text data
     * @return void
     */
    protected function _writeTextData($text)
    {
        $this->xmlWriter->text($text);
    }

    /**
     * Close an previously opened XML element
     *
     * @param string $name
     * @return XmlWriter
     */
    protected function _closeElement($name)
    {
        $this->xmlWriter->endElement();

        return $this;
    }

    /**
     * Emit XML document
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->xmlWriter->flush(false);
    }
}
