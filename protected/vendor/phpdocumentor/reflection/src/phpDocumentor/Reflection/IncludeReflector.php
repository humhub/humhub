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

use Exception;
use PHPParser_Node_Expr_Include;

class IncludeReflector extends BaseReflector
{
    /** @var PHPParser_Node_Expr_Include */
    protected $node;

    /**
     * Returns the type of this include.
     *
     * Valid types are:
     * - Include
     * - Include Once
     * - Require
     * - Require Once
     *
     * @throws Exception if the include is of an unknown type
     *
     * @return string
     */
    public function getType()
    {
        switch ($this->node->type) {
            case PHPParser_Node_Expr_Include::TYPE_INCLUDE:
                return 'Include';
            case PHPParser_Node_Expr_Include::TYPE_INCLUDE_ONCE:
                return 'Include Once';
            case PHPParser_Node_Expr_Include::TYPE_REQUIRE:
                return 'Require';
            case PHPParser_Node_Expr_Include::TYPE_REQUIRE_ONCE:
                return 'Require Once';
            default:
                throw new Exception(
                    'Unknown include type detected: '.$this->node->type
                );
        }
    }

    public function getShortName()
    {
        return (string) $this->node->expr->value;
    }
}
