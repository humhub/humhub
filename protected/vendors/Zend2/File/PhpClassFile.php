<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\File;

use SplFileInfo;

/**
 * Locate files containing PHP classes, interfaces, abstracts or traits
 */
class PhpClassFile extends SplFileInfo
{
    /**
     * @var array
     */
    protected $classes = array();

    /**
     * @var array
     */
    protected $namespaces = array();

    /**
     * Get classes
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Get namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Add class
     *
     * @param  string $class
     * @return self
     */
    public function addClass($class)
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * Add namespace
     *
     * @param  string $namespace
     * @return self
     */
    public function addNamespace($namespace)
    {
        if (in_array($namespace, $this->namespaces)) {
            return $this;
        }
        $this->namespaces[] = $namespace;
        return $this;
    }
}
