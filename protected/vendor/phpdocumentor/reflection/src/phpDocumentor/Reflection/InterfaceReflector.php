<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Interface;

class InterfaceReflector extends BaseReflector
{
    /** @var PHPParser_Node_Stmt_Interface|PHPParser_Node_Stmt_Class */
    protected $node;

    /**
     * @var ClassReflector\ConstantReflector[]
     */
    protected $constants = array();
    /**
     * @var ClassReflector\PropertyReflector[]
     */
    protected $properties = array();
    /**
     * @var ClassReflector\MethodReflector[]
     */
    protected $methods = array();

    public function parseSubElements()
    {
        foreach ($this->node->stmts as $stmt) {
            switch (get_class($stmt)) {
                case 'PHPParser_Node_Stmt_Property':
                    foreach ($stmt->props as $property) {
                        $this->properties[] = new ClassReflector\PropertyReflector(
                            $stmt,
                            $this->context,
                            $property
                        );
                    }
                    break;
                case 'PHPParser_Node_Stmt_ClassMethod':
                    $this->methods[strtolower($stmt->name)] = new ClassReflector\MethodReflector(
                        $stmt,
                        $this->context
                    );
                    break;
                case 'PHPParser_Node_Stmt_ClassConst':
                    foreach ($stmt->consts as $constant) {
                        $this->constants[] = new ClassReflector\ConstantReflector(
                            $stmt,
                            $this->context,
                            $constant
                        );
                    }
                    break;
            }
        }
    }

    public function getParentInterfaces()
    {
        $names = array();
        if ($this->node instanceof PHPParser_Node_Stmt_Interface
            && $this->node->extends
        ) {
            /** @var PHPParser_Node_Name */
            foreach ($this->node->extends as $node) {
                $names[] = '\\'.(string) $node;
            }
        }

        return $names;
    }

    /**
     * @return ClassReflector\ConstantReflector[]
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * @return ClassReflector\PropertyReflector[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return ClassReflector\MethodReflector[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string $name the method name
     * @return ClassReflector\MethodReflector|null
     */
    public function getMethod($name)
    {
        $name = strtolower($name);

        return isset($this->methods[$name]) ? $this->methods[$name] : null;
    }
}
