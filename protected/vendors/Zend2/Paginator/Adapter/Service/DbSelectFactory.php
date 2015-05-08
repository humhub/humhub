<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator\Adapter\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DbSelectFactory implements FactoryInterface
{
    /**
     * Adapter options
     * @var array
     */
    protected $creationOptions;

    /**
     * Construct with adapter options
     * @param array $creationOptions
     */
    public function __construct(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Zend\Navigation\Navigation
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $class = new \ReflectionClass('Zend\Paginator\Adapter\DbSelect');
        return $class->newInstanceArgs($this->creationOptions);
    }
}
