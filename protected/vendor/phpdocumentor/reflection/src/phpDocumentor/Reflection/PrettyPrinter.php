<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use PHPParser_Node_Scalar_String;
use PHPParser_PrettyPrinter_Zend;

/**
 * Custom PrettyPrinter for phpDocumentor.
 *
 * phpDocumentor has a custom PrettyPrinter for PHP-Parser because it needs the
 * unmodified value for Scalar variables instead of an interpreted version.
 *
 * If the interpreted version was to be used then the XML interpretation would
 * fail because of special characters.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class PrettyPrinter extends PHPParser_PrettyPrinter_Zend
{
    /**
     * Converts the string into it's original representation without converting
     * the special character combinations.
     *
     * This method is overridden from the original Zend Pretty Printer because
     * the original returns the strings as interpreted by PHP-Parser.
     * Since we do not want such conversions we take the original that is
     * injected by our own custom Lexer.
     *
     * @param PHPParser_Node_Scalar_String $node The node to return a string
     *     representation of.
     *
     * @see Lexer where the originalValue is injected.
     *
     * @return string
     */
    public function pScalar_String(PHPParser_Node_Scalar_String $node)
    {
        if (method_exists($this, 'pSafe')) {
            return $this->pSafe($node->getAttribute('originalValue'));
        }
        
        return $this->pNoIndent($node->getAttribute('originalValue'));
    }

}
