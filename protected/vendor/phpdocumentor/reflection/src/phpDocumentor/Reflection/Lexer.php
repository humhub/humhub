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

use PHPParser_Lexer;
use PHPParser_Parser;

/**
 * Custom lexer for phpDocumentor.
 *
 * phpDocumentor has a custom Lexer for PHP-Parser because it needs
 * unmodified value for Scalar variables instead of an interpreted version.
 *
 * If the interpreted version was to be used then the XML interpretation would
 * fail because of special characters.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class Lexer extends PHPParser_Lexer
{
    /**
     * Retrieves the next token and determines the associated attributes and
     * returns the token id.
     *
     * @param string   $value
     * @param string[] $startAttributes
     * @param string[] $endAttributes
     *
     * @return int
     */
    public function getNextToken(
        &$value = null,
        &$startAttributes = null,
        &$endAttributes = null
    ) {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($this->isTokenScalar($tokenId)) {
            // store original value because the value itself will be interpreted
            // by PHP_Parser and we want the unformatted value
            $endAttributes['originalValue'] = $value;
        }

        return $tokenId;
    }

    /**
     * Returns whether the given token id is a scalar that will be interpreted
     * by PHP-Parser.
     *
     * @param int $tokenId The id to check, must match a \PHPParser_Parser::T_*
     *     constant.
     *
     * @return bool
     */
    protected function isTokenScalar($tokenId)
    {
        return $tokenId == PHPParser_Parser::T_CONSTANT_ENCAPSED_STRING
            || $tokenId == PHPParser_Parser::T_LNUMBER
            || $tokenId == PHPParser_Parser::T_DNUMBER;
    }
}
