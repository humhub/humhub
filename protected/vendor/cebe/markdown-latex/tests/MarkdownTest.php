<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\latex\tests;

use cebe\markdown\latex\Markdown;

/**
 * Test case for traditional markdown.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @group default
 */
class MarkdownTest extends BaseMarkdownLatexTest
{
	public function createMarkdown()
	{
		return new Markdown();
	}

	public function getDataPaths()
	{
		return [
			'markdown-data' => __DIR__ . '/markdown-data',
		];
	}
}
