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

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use PHPParser_Node_Const;
use PHPParser_Node_Stmt_Const;

/**
 * Provides Static Reflection for file-level constants.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ConstantReflector extends BaseReflector
{
    /** @var PHPParser_Node_Stmt_Const */
    protected $constant;

    /** @var PHPParser_Node_Const */
    protected $node;

    /**
     * Registers the Constant Statement and Node with this reflector.
     *
     * @param PHPParser_Node_Stmt_Const $stmt
     * @param PHPParser_Node_Const      $node
     */
    public function __construct(
        PHPParser_Node_Stmt_Const $stmt,
        Context $context,
        PHPParser_Node_Const $node
    ) {
        parent::__construct($node, $context);
        $this->constant = $stmt;
    }

    /**
     * Returns the value contained in this Constant.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getRepresentationOfValue($this->node->value);
    }

    /**
     * Returns the parsed DocBlock.
     *
     * @return DocBlock|null
     */
    public function getDocBlock()
    {
        return $this->extractDocBlock($this->constant);
    }
}
