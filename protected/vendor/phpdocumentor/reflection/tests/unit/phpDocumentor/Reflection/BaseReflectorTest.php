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
use PHPUnit_Framework_TestCase;

/**
 * Class for testing base reflector.
 *
 * @author    Erik Baars <baarserik@hotmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class BaseReflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the setNameSpace method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::setNameSpace
     * @covers \phpDocumentor\Reflection\BaseReflector::getNameSpace
     *
     * @return void
     */
    public function testSetNameSpace()
    {
        /** @var BaseReflector $base_reflector  */
        $base_reflector = new BaseReflectorMock(
            $this->getMock('PHPParser_Node_Stmt'),
            new Context()
        );
        $base_reflector->setNamespace('namespace_name');

        $this->assertEquals('namespace_name', $base_reflector->getNameSpace());
    }

    /**
     * Tests the setNameSpace method when an invalid argument is passed
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::setNameSpace
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testSetNameSpaceInvalidArgument()
    {
        /** @var BaseReflector $base_reflector  */
        $base_reflector = new BaseReflectorMock(
            $this->getMock('PHPParser_Node_Stmt'),
            new Context()
        );
        $base_reflector->setNamespace(null);
    }

    /**
     * Tests the getDocblock method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getDocBlock
     *
     * @return void
     */
    public function testGetDocBlock()
    {
        $this->markTestIncomplete();
    }

    /**
     * Tests the getName method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getName
     *
     * @return void
     */
    public function testGetName()
    {
        $this->markTestIncomplete();
    }

    /**
     * Tests the getShortName method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getShortName
     *
     * @return void
     */
    public function testGetShortName()
    {
        $node = new NodeStmtMock();
        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals($node->__toString(), $base_reflector->getShortName());

        $node->setName('test_name');

        $this->assertEquals('test_name', $base_reflector->getShortName());
    }

    /**
     * Tests the getNameSpaceAlias method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getNamespaceAliases
     * @covers \phpDocumentor\Reflection\BaseReflector::setNamespaceAliases
     *
     * @return void
     */
    public function testGetNamespaceAliases()
    {
        $node = new NodeStmtMock();
        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals(array(), $base_reflector->getNamespaceAliases());

        $base_reflector->setNamespaceAliases(
            array('test_namespace', 'test_namespace_2')
        );

        $this->assertCount(2, $base_reflector->getNamespaceAliases());
        $this->assertEquals(
            array('\test_namespace', '\test_namespace_2'),
            $base_reflector->getNamespaceAliases()
        );
    }

    /**
     * Tests the getNameSpaceAlias method
     *
     * Tests the following scenarios:
     * - no namespace aliases set yet
     * - overwrite the current namespace alias
     * - add another namespace alias without overwriting the already set alias
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getNamespaceAliases
     * @covers \phpDocumentor\Reflection\BaseReflector::setNamespaceAlias
     *
     * @return void
     */
    public function testsetNamespaceAlias()
    {
        $node = new NodeStmtMock();

        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals(array(), $base_reflector->getNamespaceAliases());

        $base_reflector->setNamespaceAlias('test_alias', 'test_namespace');

        $namespace_aliases = $base_reflector->getNamespaceAliases();
        $this->assertCount(1, $namespace_aliases);
        $this->assertArrayHasKey('test_alias', $namespace_aliases);
        $this->assertEquals('\test_namespace', $namespace_aliases['test_alias']);

        $base_reflector->setNamespaceAlias('test_alias', 'test_namespace_2');

        $namespace_aliases = $base_reflector->getNamespaceAliases();
        $this->assertCount(1, $namespace_aliases);
        $this->assertArrayHasKey('test_alias', $namespace_aliases);
        $this->assertEquals('\test_namespace_2', $namespace_aliases['test_alias']);

        $base_reflector->setNamespaceAlias('test_alias2', 'test_namespace');

        $namespace_aliases = $base_reflector->getNamespaceAliases();
        $this->assertCount(2, $namespace_aliases);
        $this->assertArrayHasKey('test_alias', $namespace_aliases);
        $this->assertArrayHasKey('test_alias2', $namespace_aliases);
        $this->assertEquals('\test_namespace_2', $namespace_aliases['test_alias']);
        $this->assertEquals('\test_namespace', $namespace_aliases['test_alias2']);
    }

    /**
     * Tests the getLinenumber method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getLinenumber
     *
     * @return void
     */
    public function testGetLinenumber()
    {
        $node = new NodeStmtMock();

        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals($node->getLine(), $base_reflector->getLinenumber());

        $node->setLine(123);

        $this->assertEquals(123, $base_reflector->getLinenumber());
    }

    /**
     * Tests the setDefaultPackageName method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::setDefaultPackageName
     * @covers \phpDocumentor\Reflection\BaseReflector::getDefaultPackageName
     *
     * @return void
     */
    public function testSetDefaultPackageName()
    {
        $node = new NodeStmtMock();

        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertInternalType(
            'string',
            $base_reflector->getDefaultPackageName()
        );
        $this->assertEquals('', $base_reflector->getDefaultPackageName());

        $base_reflector->setDefaultPackageName('test_name');

        $this->assertEquals('test_name', $base_reflector->getDefaultPackageName());
    }

    /**
     * Tests the setDefaultPackageName method
     *
     * @covers \phpDocumentor\Reflection\BaseReflector::getRepresentationOfValue
     *
     * @return void
     */
    public function testGetRepresentationOfValue()
    {
        $node = new NodeStmtMock();
        $base_reflector = new BaseReflectorMock(
            $node,
            new Context()
        );

        $this->assertEquals('', $base_reflector->getRepresentationOfValueMock(null));

        $pretty_printer = $this->getMock(
            '\phpDocumentor\Reflection\PrettyPrinter',
            array('prettyPrintExpr')
        );
        $base_reflector->setPrettyPrinter($pretty_printer);
        $pretty_printer
            ->expects($this->once())
            ->method('prettyPrintExpr')
            ->will($this->returnValue('test_output'));

        $this->assertEquals(
            'test_output',
            $base_reflector->getRepresentationOfValueMock(new NodeExprMock())
        );
    }
}
