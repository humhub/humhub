<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\JsonRenderer;

class ViewJsonRendererFactory implements FactoryInterface
{
    /**
     * Create and return the JSON view renderer
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return JsonRenderer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $jsonRenderer = new JsonRenderer();
        return $jsonRenderer;
    }
}
