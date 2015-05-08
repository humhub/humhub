<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Session container abstract service factory.
 *
 * Allows creating Container instances, using the Zend\Service\ManagerInterface
 * if present. Containers are named in a "session_containers" array in the
 * Config service:
 *
 * <code>
 * return array(
 *     'session_containers' => array(
 *         'SessionContainer\sample',
 *         'my_sample_session_container',
 *         'MySessionContainer',
 *     ),
 * );
 * </code>
 *
 * <code>
 * $container = $services->get('MySessionContainer');
 * </code>
 */
class ContainerAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * Cached container configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Configuration key in which session containers live
     *
     * @var string
     */
    protected $configKey = 'session_containers';

    /**
     * @var \Zend\Session\ManagerInterface
     */
    protected $sessionManager;

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        $containerName = $this->normalizeContainerName($requestedName);
        return array_key_exists($containerName, $config);
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return Container
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $manager = $this->getSessionManager($services);
        return new Container($requestedName, $manager);
    }

    /**
     * Retrieve config from service locator, and cache for later
     *
     * @param  ServiceLocatorInterface $services
     * @return false|array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey]) || !is_array($config[$this->configKey])) {
            $this->config = array();
            return $this->config;
        }

        $config = $config[$this->configKey];
        $config = array_flip($config);

        $this->config = array_change_key_case($config);

        return $this->config;
    }

    /**
     * Retrieve the session manager instance, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return null|\Zend\Session\ManagerInterface
     */
    protected function getSessionManager(ServiceLocatorInterface $services)
    {
        if ($this->sessionManager !== null) {
            return $this->sessionManager;
        }

        if ($services->has('Zend\Session\ManagerInterface')) {
            $this->sessionManager = $services->get('Zend\Session\ManagerInterface');
        }

        return $this->sessionManager;
    }

    /**
     * Normalize the container name in order to perform a lookup
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeContainerName($name)
    {
        return strtolower($name);
    }
}
