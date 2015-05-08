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
 * Memory value container interface
 */
interface ContainerInterface
{
    /**
     * Get string value reference
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @return &string
     */
    public function &getRef();

    /**
     * Signal, that value is updated by external code.
     *
     * Should be used together with getRef()
     */
    public function touch();

    /**
     * Lock object in memory.
     */
    public function lock();

    /**
     * Unlock object
     */
    public function unlock();

    /**
     * Return true if object is locked
     *
     * @return bool
     */
    public function isLocked();
}
