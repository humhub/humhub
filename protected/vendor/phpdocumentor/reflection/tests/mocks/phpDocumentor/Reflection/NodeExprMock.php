<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace phpDocumentor\Reflection;

use PHPParser_Node_Expr;

/**
 * Class for testing PHPParser_Node_Expr.
 *
 * Extends the PHPParser_Node_Expr so properties and abstract methods can be mocked,
 * and therefore tested.
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class NodeExprMock extends PHPParser_Node_Expr
{
}
