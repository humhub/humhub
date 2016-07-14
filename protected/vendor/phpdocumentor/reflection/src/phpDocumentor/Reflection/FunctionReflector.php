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

use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\DocBlock\Context;
use PHPParser_Node_Param;
use PHPParser_Node_Stmt;
use PHPParser_Node_Stmt_Function;

/**
 * Provides Static Reflection for functions.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class FunctionReflector extends BaseReflector
{
    /** @var PHPParser_Node_Stmt_Function */
    protected $node;

    /** @var FunctionReflector\ArgumentReflector[] */
    protected $arguments = array();

    /**
     * Initializes the reflector using the function statement object of
     * PHP-Parser.
     *
     * @param PHPParser_Node_Stmt $node    Function object coming from PHP-Parser.
     * @param Context             $context The context in which the node occurs.
     */
    public function __construct(PHPParser_Node_Stmt $node, Context $context)
    {
        parent::__construct($node, $context);

        /** @var PHPParser_Node_Param $param  */
        foreach ($node->params as $param) {
            $reflector = new FunctionReflector\ArgumentReflector(
                $param,
                $context
            );
            $this->arguments[$reflector->getName()] = $reflector;
        }
    }

    /**
     * Checks whether the function returns a value by reference.
     *
     * @return bool TRUE if the return value is by reference, FALSE otherwise.
     */
    public function isByRef()
    {
        return $this->node->byRef;
    }

    /**
     * Returns a list of Argument objects.
     *
     * @return FunctionReflector\ArgumentReflector[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
