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

use phpDocumentor\Reflection\DocBlock\Context;
use PHPParser_Node_Stmt_Class;
use PHPUnit_Framework_TestCase;

/**
 * Class for testing ClassReflector.
 *
 * @author    Erik Baars <baarserik@hotmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class ClassReflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::parseSubElements
     *
     * @return void
     */
    public function testParseSubElements()
    {
        $this->markTestIncomplete();
    }

    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::isAbstract
     *
     * @return void
     */
    public function testIsAbstract()
    {
        //$this->markTestSkipped();
        $node = new NodeStmtMock2();
        $class_reflector = new ClassReflector(
            $node,
            new Context()
        );

        $this->assertFalse($class_reflector->isAbstract());

        $node->type = PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT;
        $this->assertTrue($class_reflector->isAbstract());
    }

    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::isFinal
     *
     * @return void
     */
    public function testIsFinal()
    {
        $node = new NodeStmtMock2();
        $class_reflector = new ClassReflector(
            $node,
            new Context()
        );

        $this->assertFalse($class_reflector->isFinal());

        $node->type = PHPParser_Node_Stmt_Class::MODIFIER_FINAL;
        $this->assertTrue($class_reflector->isFinal());
    }

    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::getTraits
     *
     * @return void
     */
    public function testGetTraits()
    {
        $node = new NodeStmtMock();
        $class_reflector = new ClassReflectorMock(
            $node,
            new Context()
        );

        $traits = $class_reflector->getTraits();
        $this->assertInternalType('array', $traits);
        $this->assertEmpty($traits);

        $class_reflector->setTraits(array('trait1', 'trait2'));
        $traits = $class_reflector->getTraits();

        $this->assertCount(2, $traits);
        $this->assertEquals('trait1', reset($traits));
    }

    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::getParentClass
     *
     * @return void
     */
    public function testGetParentClass()
    {
        $node = new NodeStmtMock();
        $class_reflector = new ClassReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals('', $class_reflector->getParentClass());

        $node->extends = 'dummy';

        $this->assertEquals('\dummy', $class_reflector->getParentClass());
    }

    /**
     * Tests the parseSubElements method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::getInterfaces
     *
     * @return void
     */
    public function testGetInterfaces()
    {
        $node = new NodeStmtMock();
        $class_reflector = new ClassReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals(array(), $class_reflector->getInterfaces());

        $node->implements = array('dummy');

        $this->assertEquals(array('\dummy'), $class_reflector->getInterfaces());
    }

    /**
     * Tests the getMethod method
     *
     * @covers \phpDocumentor\Reflection\ClassReflector::getMethod
     *
     * @return void
     */
    public function testGetMethod()
    {
        $node = new NodeStmtMock();
        $node->stmts = array(new \PHPParser_Node_Stmt_ClassMethod('someMethod'));
        $class_reflector = new ClassReflectorMock(
            $node,
            new Context()
        );

        // Before parseSubElements
        $this->assertNull($class_reflector->getMethod('someMethod'));

        $class_reflector->parseSubElements();

        // After parseSubElements
        $this->assertInstanceOf(
            '\phpDocumentor\Reflection\ClassReflector\MethodReflector',
            $class_reflector->getMethod('someMethod')
        );
        $this->assertNull($class_reflector->getMethod('someOtherMethod'));
    }
}
