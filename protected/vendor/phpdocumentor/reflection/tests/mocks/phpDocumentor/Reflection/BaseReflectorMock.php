<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Erik Baars <baarserik@hotmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace phpDocumentor\Reflection;

use PHPParser_Node_Expr;

/**
 * Class for testing base reflector.
 *
 * Extends the baseReflector so properties and abstract methods can be mocked,
 * and therefore tested.
 *
 * @author    Erik Baars <baarserik@hotmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class BaseReflectorMock extends BaseReflector
{
    /**
     * Overload method so we can test the protected method
     *
     * @param PHPParser_Node_Expr $value
     *
     * @return string
     */
    public function getRepresentationOfValueMock(
        PHPParser_Node_Expr $value = null
    ) {
        return parent::getRepresentationOfValue($value);
    }

    /**
     * @param $val
     *
     * @return void
     */
    public function setPrettyPrinter($val)
    {
        self::$prettyPrinter = $val;
    }
}
