<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Iterator;

interface IteratorInterface extends Iterator
{

    const CURRENT_AS_SELF     = 0;
    const CURRENT_AS_KEY      = 1;
    const CURRENT_AS_VALUE    = 2;
    const CURRENT_AS_METADATA = 3;

    /**
     * Get storage instance
     *
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * Get iterator mode
     *
     * @return int Value of IteratorInterface::CURRENT_AS_*
     */
    public function getMode();

    /**
     * Set iterator mode
     *
     * @param int $mode Value of IteratorInterface::CURRENT_AS_*
     * @return IteratorInterface Fluent interface
     */
    public function setMode($mode);
}
