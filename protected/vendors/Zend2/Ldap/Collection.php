<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap;

use Countable;
use Iterator;

/**
 * Zend\Ldap\Collection wraps a list of LDAP entries.
 */
class Collection implements Iterator, Countable
{
    /**
     * Iterator
     *
     * @var Collection\DefaultIterator
     */
    protected $iterator = null;

    /**
     * Current item number
     *
     * @var int
     */
    protected $current = -1;

    /**
     * Container for item caching to speed up multiple iterations
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Constructor.
     *
     * @param Collection\DefaultIterator $iterator
     */
    public function __construct(Collection\DefaultIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        return $this->iterator->close();
    }

    /**
     * Get all entries as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this as $item) {
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Get first entry
     *
     * @return array
     */
    public function getFirst()
    {
        if ($this->count() > 0) {
            $this->rewind();
            return $this->current();
        }
        return null;
    }

    /**
     * Returns the underlying iterator
     *
     * @return Collection\DefaultIterator
     */
    public function getInnerIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws Exception\LdapException
     */
    public function current()
    {
        if ($this->count() > 0) {
            if ($this->current < 0) {
                $this->rewind();
            }
            if (!array_key_exists($this->current, $this->cache)) {
                $current = $this->iterator->current();
                if ($current === null) {
                    return null;
                }
                $this->cache[$this->current] = $this->createEntry($current);
            }
            return $this->cache[$this->current];
        }
        return null;
    }

    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return array
     */
    protected function createEntry(array $data)
    {
        return $data;
    }

    /**
     * Return the current result item DN
     *
     * @return string|null
     */
    public function dn()
    {
        if ($this->count() > 0) {
            if ($this->current < 0) {
                $this->rewind();
            }
            return $this->iterator->key();
        }
        return null;
    }

    /**
     * Return the current result item key
     * Implements Iterator
     *
     * @return int|null
     */
    public function key()
    {
        if ($this->count() > 0) {
            if ($this->current < 0) {
                $this->rewind();
            }
            return $this->current;
        }
        return null;
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     * @throws Exception\LdapException
     */
    public function next()
    {
        $this->iterator->next();
        $this->current++;
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     * @throws Exception\LdapException
     */
    public function rewind()
    {
        $this->iterator->rewind();
        $this->current = 0;
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return bool
     */
    public function valid()
    {
        if (isset($this->cache[$this->current])) {
            return true;
        }
        return $this->iterator->valid();
    }
}
