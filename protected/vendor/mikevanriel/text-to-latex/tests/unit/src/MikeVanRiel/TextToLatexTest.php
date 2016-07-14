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
 * Tests the TextToLatex class.
 */
class TextToLatexTest extends \PHPUnit_Framework_TestCase
{
    /** @var TextToLatex */
    protected $fixture;

    /**
     * Instantiates a default TextToLatex object.
     */
    public function setUp()
    {
        $this->fixture = new TextToLatex();
    }

    /**
     * @param string $character The character to test.
     *
     * @dataProvider provideReservedCharacters
     * @covers MikeVanRiel::TextToLatex
     */
    public function testConvertsReservedCharactersToEscapedVersion($character)
    {
        $this->assertSame('\\'.$character, $this->fixture->convert($character));
    }

    /**
     * @covers MikeVanRiel::TextToLatex
     */
    public function testConvertBackslashToSpecialCode()
    {
        $this->assertSame('{\textbackslash}', $this->fixture->convert('\\'));
    }

    /**
     * @covers MikeVanRiel::TextToLatex
     */
    public function testConvertEllipsisToSpecialCode()
    {
        $this->assertSame('123{\ldots}456', $this->fixture->convert('123...456'));
        $this->assertSame('try{\ldots}catch', $this->fixture->convert('try...catch'));
    }

    /**
     * @covers MikeVanRiel::TextToLatex
     */
    public function testConvertDoubleQuotesToBackTicks()
    {
        $this->assertSame("``456''", $this->fixture->convert('"456"'));
    }

    /**
     * Provides all reserver characters that need to be escaped.
     *
     * @return string[][]
     */
    public function provideReservedCharacters()
    {
        return array(
            array('#'),
            array('{'),
            array('}'),
            array('_'),
            array('&'),
        );
    }
}

