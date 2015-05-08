<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use Zend\Cache\Exception;
use Zend\Cache\Storage\ClearExpiredInterface;
use Zend\Cache\Storage\PostEvent;
use Zend\EventManager\EventManagerInterface;

class ClearExpiredByFactor extends AbstractPlugin
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $callback = array($this, 'clearExpiredByFactor');

        $this->listeners[] = $events->attach('setItem.post',  $callback, $priority);
        $this->listeners[] = $events->attach('setItems.post', $callback, $priority);
        $this->listeners[] = $events->attach('addItem.post',  $callback, $priority);
        $this->listeners[] = $events->attach('addItems.post', $callback, $priority);
    }

    /**
     * Clear expired items by factor after writing new item(s)
     *
     * @param  PostEvent $event
     * @return void
     */
    public function clearExpiredByFactor(PostEvent $event)
    {
        $storage = $event->getStorage();
        if (!($storage instanceof ClearExpiredInterface)) {
            return;
        }

        $factor = $this->getOptions()->getClearingFactor();
        if ($factor && mt_rand(1, $factor) == 1) {
            $storage->clearExpired();
        }
    }
}
