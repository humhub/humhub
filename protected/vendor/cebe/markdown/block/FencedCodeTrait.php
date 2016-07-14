<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\block;

/**
 * Adds the fenced code blocks
 *
 * automatically included 4 space indented code blocks
 */
trait FencedCodeTrait
{
	use CodeTrait;

	/**
	 * identify a line as the beginning of a fenced code block.
	 */
	protected function identifyFencedCode($line)
	{
		return ($l = $line[0]) === '`' && strncmp($line, '```', 3) === 0 ||
				$l === '~' && strncmp($line, '~~~', 3) === 0;
	}

	/**
	 * Consume lines for a fenced code block
	 */
	protected function consumeFencedCode($lines, $current)
	{
		// consume until ```
		$line = rtrim($lines[$current]);
		$fence = substr($line, 0, $pos = strrpos($line, $line[0]) + 1);
		$language = substr($line, $pos);
		$content = [];
		for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			if (rtrim($line = $lines[$i]) !== $fence) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'code',
			'content' => implode("\n", $content),
		];
		if (!empty($language)) {
			$block['language'] = $language;
		}
		return [$block, $i];
	}
}
