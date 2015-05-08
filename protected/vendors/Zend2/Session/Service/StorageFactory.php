<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Service;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Storage\Exception as SessionException;
use Zend\Session\Storage\Factory;
use Zend\Session\Storage\StorageInterface;

class StorageFactory implements FactoryInterface
{
    /**
     * Create session storage object
     *
     * Uses "session_storage" section of configuration to seed a StorageInterface
     * instance. That array should contain the key "type", specifying the storage
     * type to use, and optionally "options", containing any options to be used in
     * creating the StorageInterface instance.
     *
     * @param  ServiceLocatorInterface    $services
     * @return StorageInterface
     * @throws ServiceNotCreatedException if session_storage is missing, or the
     *         factory cannot create the storage instance.
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');
        if (!isset($config['session_storage']) || !is_array($config['session_storage'])) {
            throw new ServiceNotCreatedException(
                'Configuration is missing a "session_storage" key, or the value of that key is not an array'
            );
        }

        $config = $config['session_storage'];
        if (!isset($config['type'])) {
            throw new ServiceNotCreatedException(
                '"session_storage" configuration is missing a "type" key'
            );
        }
        $type = $config['type'];
        $options = isset($config['options']) ? $config['options'] : array();

        try {
            $storage = Factory::factory($type, $options);
        } catch (SessionException $e) {
            throw new ServiceNotCreatedException(sprintf(
                'Factory is unable to create StorageInterface instance: %s',
                $e->getMessage()
            ), $e->getCode(), $e);
        }

        return $storage;
    }
}
