<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\block;

/**
 * Adds the block quote elements
 */
trait QuoteTrait
{
	/**
	 * identify a line as the beginning of a block quote.
	 */
	protected function identifyQuote($line)
	{
		return $line[0] === '>' && (!isset($line[1]) || ($l1 = $line[1]) === ' ' || $l1 === "\t");
	}

	/**
	 * Consume lines for a blockquote element
	 */
	protected function consumeQuote($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (ltrim($line) !== '') {
				if ($line[0] == '>' && !isset($line[1])) {
					$line = '';
				} elseif (strncmp($line, '> ', 2) === 0) {
					$line = substr($line, 2);
				}
				$content[] = $line;
			} else {
				break;
			}
		}

		$block = [
			'quote',
			'content' => $this->parseBlocks($content),
			'simple' => true,
		];
		return [$block, $i];
	}


	/**
	 * Renders a blockquote
	 */
	protected function renderQuote($block)
	{
		return '<blockquote>' . $this->renderAbsy($block['content']) . "</blockquote>\n";
	}

	abstract protected function parseBlocks($lines);
	abstract protected function renderAbsy($absy);
}
