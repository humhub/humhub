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
use Zend\Cache\Storage\OptimizableInterface;
use Zend\Cache\Storage\PostEvent;
use Zend\EventManager\EventManagerInterface;

class OptimizeByFactor extends AbstractPlugin
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $callback          = array($this, 'optimizeByFactor');
        $this->listeners[] = $events->attach('removeItem.post',  $callback, $priority);
        $this->listeners[] = $events->attach('removeItems.post', $callback, $priority);
    }

    /**
     * Optimize by factor on a success _RESULT_
     *
     * @param  PostEvent $event
     * @return void
     */
    public function optimizeByFactor(PostEvent $event)
    {
        $storage = $event->getStorage();
        if (!($storage instanceof OptimizableInterface)) {
            return;
        }

        $factor = $this->getOptions()->getOptimizingFactor();
        if ($factor && mt_rand(1, $factor) == 1) {
            $storage->optimize();
        }
    }
}
