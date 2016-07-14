<?php
namespace phpDocumentor\Reflection\Event;

use phpDocumentor\Event\EventAbstract;
use phpDocumentor\Reflection\DocBlock;

class PostDocBlockExtractionEvent extends EventAbstract
{
    /** @var DocBlock */
    protected $docblock = null;

    /**
     * @param DocBlock $docblock
     *
     * @return $this
     */
    public function setDocblock(DocBlock $docblock = null)
    {
        $this->docblock = $docblock;

        return $this;
    }

    /**
     * @return DocBlock|null
     */
    public function getDocblock()
    {
        return $this->docblock;
    }
}
