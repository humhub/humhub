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

use PHPParser_Node_Scalar_String;
use PHPUnit_Framework_TestCase;

/**
 * Class for testing the PrettyPrinter.
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class PrettyPrinterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \phpDocumentor\Reflection\PrettyPrinter::pScalar_String
     *
     * @return void
     */
    public function testScalarStringPrinting()
    {
        $object = new PrettyPrinter();
        $this->assertEquals(
            'Another value',
            $object->pScalar_String(
                new PHPParser_Node_Scalar_String(
                    'Value',
                    array('originalValue' => 'Another value')
                )
            )
        );
    }
}
