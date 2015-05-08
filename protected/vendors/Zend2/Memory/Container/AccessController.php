<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Memory\Container;

/**
 * Memory object container access controller.
 *
 * Memory manager stores a list of generated objects to control them.
 * So container objects always have at least one reference and can't be automatically destroyed.
 *
 * This class is intended to be an userland proxy to memory container object.
 * It's not referenced by memory manager and class destructor is invoked immediately after going
 * out of scope or unset operation.
 *
 * Class also provides Zend\Memory\Container interface and works as proxy for such cases.
 */
class AccessController implements ContainerInterface
{
    /**
     * Memory container object
     *
     * @var Movable
     */
    private $memContainer;


    /**
     * Object constructor
     *
     * @param Movable $memContainer
     */
    public function __construct(Movable $memContainer)
    {
        $this->memContainer = $memContainer;
    }

    /**
     * Object destructor
     */
    public function __destruct()
    {
        $this->memContainer->destroy();
    }


    /**
     * Get string value reference
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @return &string
     */
    public function &getRef()
    {
        return $this->memContainer->getRef();
    }

    /**
     * Signal, that value is updated by external code.
     *
     * Should be used together with getRef()
     */
    public function touch()
    {
        $this->memContainer->touch();
    }

    /**
     * Lock object in memory.
     */
    public function lock()
    {
        $this->memContainer->lock();
    }


    /**
     * Unlock object
     */
    public function unlock()
    {
        $this->memContainer->unlock();
    }

    /**
     * Return true if object is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->memContainer->isLocked();
    }

    /**
     * Get handler
     *
     * Loads object if necessary and moves it to the top of loaded objects list.
     * Swaps objects from the bottom of loaded objects list, if necessary.
     *
     * @param string $property
     * @return string
     */
    public function __get($property)
    {
        return $this->memContainer->$property;
    }

    /**
     * Set handler
     *
     * @param string $property
     * @param  string $value
     */
    public function __set($property, $value)
    {
        $this->memContainer->$property = $value;
    }
}
