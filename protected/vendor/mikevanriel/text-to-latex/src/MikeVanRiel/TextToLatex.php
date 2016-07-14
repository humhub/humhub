<?php
/**
 * Mike van Riel
 *
 * PHP Version 5.0
 *
 * @copyright 2010-2013 Mike van Riel (http://www.mikevanriel.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/mvriel/TextToLatex
 */

namespace MikeVanRiel;

/**
 * Converter to transition a piece of text into a valid LaTeX block.
 *
 * This class assists in translation from ASCII text to LaTeX by escaping
 * special characters and "fixing" quote marks (using English conventions).
 *
 * @link http://www.ctan.org/pkg/txt2latex Port of the txt2latex CTAN package to PHP.
 */
class TextToLatex
{
    /**
     * Converts the given string into a valid LaTeX counterpart.
     *
     * Note: this function does not provide a LaTeX layout; you are still responsible for adding
     * your own documentclass, begin and end markers.
     *
     * @param string $string
     *
     * @return string
     */
    public function convert($string)
    {
        $string = str_replace(
            array(
                '\\',
                '{',
                '}',
                '\\{\\textbackslash\\}',
                '$',
                '_',
                '&',
                '#',
            ),
            array(
                '{\\textbackslash}',
                '\\{',
                '\\}',
                '{\\textbackslash}',
                '\\$',
                '\\_',
                '\\&',
                '\\#',
            ),
            $string
        );

        $string = preg_replace(
            array(
                '/(^|[^.])\.\.\.([^.])/',
                '/(^|\\s)"/',
                '/"(\\W|$)/',
                "/(^|\\s)'/"
            ),
            array(
                '\\1{\\ldots}\\2',
                '\\1``',
                "''\\1",
                '\1`'
            ),
            $string
        );

        return $string;
    }
}

