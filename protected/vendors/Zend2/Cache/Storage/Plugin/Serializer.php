<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use stdClass;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\PostEvent;
use Zend\EventManager\EventManagerInterface;

class Serializer extends AbstractPlugin
{
    /**
     * @var array
     */
    protected $capabilities = array();

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // The higher the priority the sooner the plugin will be called on pre events
        // but the later it will be called on post events.
        $prePriority  = $priority;
        $postPriority = -$priority;

        // read
        $this->listeners[] = $events->attach('getItem.post',  array($this, 'onReadItemPost'), $postPriority);
        $this->listeners[] = $events->attach('getItems.post', array($this, 'onReadItemsPost'), $postPriority);

        // write
        $this->listeners[] = $events->attach('setItem.pre',  array($this, 'onWriteItemPre'), $prePriority);
        $this->listeners[] = $events->attach('setItems.pre', array($this, 'onWriteItemsPre'), $prePriority);

        $this->listeners[] = $events->attach('addItem.pre',  array($this, 'onWriteItemPre'), $prePriority);
        $this->listeners[] = $events->attach('addItems.pre', array($this, 'onWriteItemsPre'), $prePriority);

        $this->listeners[] = $events->attach('replaceItem.pre',  array($this, 'onWriteItemPre'), $prePriority);
        $this->listeners[] = $events->attach('replaceItems.pre', array($this, 'onWriteItemsPre'), $prePriority);

        $this->listeners[] = $events->attach('checkAndSetItem.pre', array($this, 'onWriteItemPre'), $prePriority);

        // increment / decrement item(s)
        $this->listeners[] = $events->attach('incrementItem.pre', array($this, 'onIncrementItemPre'), $prePriority);
        $this->listeners[] = $events->attach('incrementItems.pre', array($this, 'onIncrementItemsPre'), $prePriority);

        $this->listeners[] = $events->attach('decrementItem.pre', array($this, 'onDecrementItemPre'), $prePriority);
        $this->listeners[] = $events->attach('decrementItems.pre', array($this, 'onDecrementItemsPre'), $prePriority);

        // overwrite capabilities
        $this->listeners[] = $events->attach('getCapabilities.post',  array($this, 'onGetCapabilitiesPost'), $postPriority);
    }

    /**
     * On read item post
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onReadItemPost(PostEvent $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $result     = $event->getResult();
        $result     = $serializer->unserialize($result);
        $event->setResult($result);
    }

    /**
     * On read items post
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onReadItemsPost(PostEvent $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $result     = $event->getResult();
        foreach ($result as &$value) {
            $value = $serializer->unserialize($value);
        }
        $event->setResult($result);
    }

    /**
     * On write item pre
     *
     * @param  Event $event
     * @return void
     */
    public function onWriteItemPre(Event $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $params     = $event->getParams();
        $params['value'] = $serializer->serialize($params['value']);
    }

    /**
     * On write items pre
     *
     * @param  Event $event
     * @return void
     */
    public function onWriteItemsPre(Event $event)
    {
        $serializer = $this->getOptions()->getSerializer();
        $params     = $event->getParams();
        foreach ($params['keyValuePairs'] as &$value) {
            $value = $serializer->serialize($value);
        }
    }

    /**
     * On increment item pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onIncrementItemPre(Event $event)
    {
        $storage  = $event->getTarget();
        $params   = $event->getParams();
        $casToken = null;
        $success  = null;
        $oldValue = $storage->getItem($params['key'], $success, $casToken);
        $newValue = $oldValue + $params['value'];

        if ($success) {
            $storage->checkAndSetItem($casToken, $params['key'], $oldValue + $params['value']);
            $result = $newValue;
        } else {
            $result = false;
        }

        $event->stopPropagation(true);
        return $result;
    }

    /**
     * On increment items pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onIncrementItemsPre(Event $event)
    {
        $storage       = $event->getTarget();
        $params        = $event->getParams();
        $keyValuePairs = $storage->getItems(array_keys($params['keyValuePairs']));
        foreach ($params['keyValuePairs'] as $key => & $value) {
            if (isset($keyValuePairs[$key])) {
                $keyValuePairs[$key]+= $value;
            } else {
                $keyValuePairs[$key] = $value;
            }
        }

        $failedKeys = $storage->setItems($keyValuePairs);
        foreach ($failedKeys as $failedKey) {
            unset($keyValuePairs[$failedKey]);
        }

        $event->stopPropagation(true);
        return $keyValuePairs;
    }

    /**
     * On decrement item pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onDecrementItemPre(Event $event)
    {
        $storage  = $event->getTarget();
        $params   = $event->getParams();
        $success  = null;
        $casToken = null;
        $oldValue = $storage->getItem($params['key'], $success, $casToken);
        $newValue = $oldValue - $params['value'];

        if ($success) {
            $storage->checkAndSetItem($casToken, $params['key'], $oldValue + $params['value']);
            $result = $newValue;
        } else {
            $result = false;
        }

        $event->stopPropagation(true);
        return $result;
    }

    /**
     * On decrement items pre
     *
     * @param  Event $event
     * @return mixed
     */
    public function onDecrementItemsPre(Event $event)
    {
        $storage       = $event->getTarget();
        $params        = $event->getParams();
        $keyValuePairs = $storage->getItems(array_keys($params['keyValuePairs']));
        foreach ($params['keyValuePairs'] as $key => &$value) {
            if (isset($keyValuePairs[$key])) {
                $keyValuePairs[$key]-= $value;
            } else {
                $keyValuePairs[$key] = -$value;
            }
        }

        $failedKeys = $storage->setItems($keyValuePairs);
        foreach ($failedKeys as $failedKey) {
            unset($keyValuePairs[$failedKey]);
        }

        $event->stopPropagation(true);
        return $keyValuePairs;
    }

    /**
     * On get capabilities
     *
     * @param  PostEvent $event
     * @return void
     */
    public function onGetCapabilitiesPost(PostEvent $event)
    {
        $baseCapabilities = $event->getResult();
        $index = spl_object_hash($baseCapabilities);

        if (!isset($this->capabilities[$index])) {
            $this->capabilities[$index] = new Capabilities(
                $baseCapabilities->getAdapter(),
                new stdClass(), // marker
                array('supportedDatatypes' => array(
                    'NULL'     => true,
                    'boolean'  => true,
                    'integer'  => true,
                    'double'   => true,
                    'string'   => true,
                    'array'    => true,
                    'object'   => 'object',
                    'resource' => false,
                )),
                $baseCapabilities
            );
        }

        $event->setResult($this->capabilities[$index]);
    }
}
