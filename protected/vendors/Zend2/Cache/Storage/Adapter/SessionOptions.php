<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use Zend\Cache\Exception;
use Zend\Session\Container as SessionContainer;

/**
 * These are options specific to the APC adapter
 */
class SessionOptions extends AdapterOptions
{
    /**
     * The session container
     *
     * @var null|SessionContainer
     */
    protected $sessionContainer = null;

    /**
     * Set the session container
     *
     * @param  null|SessionContainer $sessionContainer
     * @return SessionOptions
     */
    public function setSessionContainer(SessionContainer $sessionContainer = null)
    {
        if ($this->sessionContainer != $sessionContainer) {
            $this->triggerOptionEvent('session_container', $sessionContainer);
            $this->sessionContainer = $sessionContainer;
        }

        return $this;
    }

    /**
     * Get the session container
     *
     * @return null|SessionContainer
     */
    public function getSessionContainer()
    {
        return $this->sessionContainer;
    }
}
