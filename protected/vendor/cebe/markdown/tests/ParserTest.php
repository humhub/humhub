<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\tests;

use cebe\markdown\Parser;

/**
 * Test case for the parser base class.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @group default
 */
class ParserTest extends  \PHPUnit_Framework_TestCase
{
	public function testMarkerOrder()
	{
		$parser = new TestParser();
		$parser->markers = [
			'[' => 'parseMarkerA',
			'[[' => 'parseMarkerB',
		];

		$this->assertEquals("<p>Result is A</p>\n", $parser->parse('Result is [abc]'));
		$this->assertEquals("<p>Result is B</p>\n", $parser->parse('Result is [[abc]]'));
		$this->assertEquals('Result is A', $parser->parseParagraph('Result is [abc]'));
		$this->assertEquals('Result is B', $parser->parseParagraph('Result is [[abc]]'));

		$parser = new TestParser();
		$parser->markers = [
			'[[' => 'parseMarkerB',
			'[' => 'parseMarkerA',
		];

		$this->assertEquals("<p>Result is A</p>\n", $parser->parse('Result is [abc]'));
		$this->assertEquals("<p>Result is B</p>\n", $parser->parse('Result is [[abc]]'));
		$this->assertEquals('Result is A', $parser->parseParagraph('Result is [abc]'));
		$this->assertEquals('Result is B', $parser->parseParagraph('Result is [[abc]]'));
	}

	public function testMaxNestingLevel()
	{
		$parser = new TestParser();
		$parser->markers = [
			'[' => 'parseMarkerC',
		];

		$parser->maximumNestingLevel = 3;
		$this->assertEquals("(C-a(C-b(C-c)))", $parser->parseParagraph('[a[b[c]]]'));
		$parser->maximumNestingLevel = 2;
		$this->assertEquals("(C-a(C-b[c]))", $parser->parseParagraph('[a[b[c]]]'));
		$parser->maximumNestingLevel = 1;
		$this->assertEquals("(C-a[b[c]])", $parser->parseParagraph('[a[b[c]]]'));
	}
}

class TestParser extends Parser
{
	public $markers = [];

	protected function inlineMarkers()
	{
		return $this->markers;
	}

	protected function parseMarkerA($text)
	{
		return [['text', 'A'], strrpos($text, ']') + 1];
	}

	protected function parseMarkerB($text)
	{
		return [['text', 'B'], strrpos($text, ']') + 1];
	}

	protected function parseMarkerC($text)
	{
		$terminatingMarkerPos = strrpos($text, ']');
		$inside = $this->parseInline(substr($text, 1, $terminatingMarkerPos - 1));
		return [['text', '(C-' . $this->renderAbsy($inside) . ')'], $terminatingMarkerPos + 1];
	}
}
