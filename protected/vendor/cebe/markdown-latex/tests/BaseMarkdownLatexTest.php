<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\latex\tests;
use cebe\markdown\tests\BaseMarkdownTest;

/**
 * Base class for all Test cases.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
abstract class BaseMarkdownLatexTest extends BaseMarkdownTest
{
	protected $outputFileExtension = '.tex';

	public function testInvalidUtf8()
	{
//   		$m = $this->createMarkdown();
//   		$this->assertEquals('\\lstinline|�|', $m->parseParagraph("`\x80`"));
	}

}
