<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\latex\tests;

use cebe\markdown\latex\GithubMarkdown;

/**
 * Test case for the github flavored markdown.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @group github
 */
class GithubMarkdownTest extends BaseMarkdownLatexTest
{
	public function createMarkdown()
	{
		return new GithubMarkdown();
	}

	public function getDataPaths()
	{
		return [
			'markdown-data' => __DIR__ . '/markdown-data',
			'github-data' => __DIR__ . '/github-data',
		];
	}

	public function testNewlines()
	{
		$markdown = $this->createMarkdown();
		$this->assertEquals("This is text\\\\\nnewline\nnewline.", $markdown->parseParagraph("This is text  \nnewline\nnewline."));
		$markdown->enableNewlines = true;
		$this->assertEquals("This is text\\\\\nnewline\\\\\nnewline.", $markdown->parseParagraph("This is text  \nnewline\nnewline."));

		$this->assertEquals("This is text\n\nnewline\\\\\nnewline.\n\n", $markdown->parse("This is text\n\nnewline\nnewline."));
	}
}
