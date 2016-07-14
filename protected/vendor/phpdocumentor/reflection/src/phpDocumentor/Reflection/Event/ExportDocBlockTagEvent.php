<?php
namespace phpDocumentor\Reflection\Event;

use DOMNode;
use phpDocumentor\Event\EventAbstract;
use phpDocumentor\Reflection\DocBlock\Tag;

class ExportDocBlockTagEvent extends EventAbstract
{
    /** @var DOMNode */
    protected $xml = null;

    /** @var Tag */
    protected $object = null;

    /**
     * @return DOMNode|null
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @return Tag|null
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Tag $object
     *
     * @return ExportDocBlockTagEvent
     */
    public function setObject(Tag $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param DOMNode $xml
     *
     * @return ExportDocBlockTagEvent
     */
    public function setXml(DOMNode $xml = null)
    {
        $this->xml = $xml;

        return $this;
    }
}
