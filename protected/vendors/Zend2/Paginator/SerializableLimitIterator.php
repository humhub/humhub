<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use ArrayAccess;
use Iterator;
use LimitIterator;
use Serializable;

class SerializableLimitIterator extends LimitIterator implements Serializable, ArrayAccess
{

    /**
     * Offset to first element
     *
     * @var int
     */
    private $offset;

    /**
     * Maximum number of elements to show or -1 for all
     *
     * @var int
     */
    private $count;

    /**
     * Construct a Zend\Paginator\SerializableLimitIterator
     *
     * @param Iterator $it Iterator to limit (must be serializable by un-/serialize)
     * @param int $offset Offset to first element
     * @param int $count Maximum number of elements to show or -1 for all
     * @see LimitIterator::__construct
     */
    public function __construct(Iterator $it, $offset=0, $count=-1)
    {
        parent::__construct($it, $offset, $count);
        $this->offset = $offset;
        $this->count = $count;
    }

    /**
     * @return string representation of the instance
     */
    public function serialize()
    {
        return serialize(array(
            'it'     => $this->getInnerIterator(),
            'offset' => $this->offset,
            'count'  => $this->count,
            'pos'    => $this->getPosition(),
        ));
    }

    /**
     * @param string $data representation of the instance
     * @return void
     */
    public function unserialize($data)
    {
        $dataArr = unserialize($data);
        $this->__construct($dataArr['it'], $dataArr['offset'], $dataArr['count']);
        $this->seek($dataArr['pos']+$dataArr['offset']);
    }

    /**
     * Returns value of the Iterator
     *
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $currentOffset = $this->key();
        $this->seek($offset);
        $current = $this->current();
        $this->seek($currentOffset);
        return $current;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Determine if a value of Iterator is set and is not NULL
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if ($offset > 0 && $offset < $this->count) {
            try {
                $currentOffset = $this->key();
                $this->seek($offset);
                $current = $this->current();
                $this->seek($currentOffset);
                return null !== $current;
            } catch (\OutOfBoundsException $e) {
                // reset position in case of exception is assigned null
                $this->seek($currentOffset);
                return false;
            }
        }
        return false;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
    }
}
