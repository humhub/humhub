<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Session;

use Zend\EventManager\EventManager;
use Zend\Session\Storage\StorageInterface as Storage;
use Zend\Session\Validator\ValidatorInterface as Validator;

/**
 * Validator chain for validating sessions
 */
class ValidatorChain extends EventManager
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Construct the validation chain
     *
     * Retrieves validators from session storage and attaches them.
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;

        $validators = $storage->getMetadata('_VALID');
        if ($validators) {
            foreach ($validators as $validator => $data) {
                $this->attach('session.validate', array(new $validator($data), 'isValid'));
            }
        }
    }

    /**
     * Attach a listener to the session validator chain
     *
     * @param  string $event
     * @param  callable $callback
     * @param  int $priority
     * @return \Zend\Stdlib\CallbackHandler
     */
    public function attach($event, $callback = null, $priority = 1)
    {
        $context = null;
        if ($callback instanceof Validator) {
            $context = $callback;
        } elseif (is_array($callback)) {
            $test = array_shift($callback);
            if ($test instanceof Validator) {
                $context = $test;
            }
            array_unshift($callback, $test);
        }
        if ($context instanceof Validator) {
            $data = $context->getData();
            $name = $context->getName();
            $this->getStorage()->setMetadata('_VALID', array($name => $data));
        }

        $listener = parent::attach($event, $callback, $priority);
        return $listener;
    }

    /**
     * Retrieve session storage object
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
