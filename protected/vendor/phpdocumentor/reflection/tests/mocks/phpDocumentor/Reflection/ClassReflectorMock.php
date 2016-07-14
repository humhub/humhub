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

/**
 * Class for testing ClassReflector.
 *
 * Extends the ClassReflector so properties and abstract methods can be mocked,
 * and therefore tested.
 *
 * @author    Erik Baars <baarserik@hotmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class ClassReflectorMock extends ClassReflector
{
    public function setTraits(array $val)
    {
        $this->traits = $val;
    }
}
