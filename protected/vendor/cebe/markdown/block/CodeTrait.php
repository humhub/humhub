<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\block;

/**
 * Adds the 4 space indented code blocks
 */
trait CodeTrait
{
	/**
	 * identify a line as the beginning of a code block.
	 */
	protected function identifyCode($line)
	{
		// indentation >= 4 or one tab is code
		return ($l = $line[0]) === ' ' && $line[1] === ' ' && $line[2] === ' ' && $line[3] === ' ' || $l === "\t";
	}

	/**
	 * Consume lines for a code block element
	 */
	protected function consumeCode($lines, $current)
	{
		// consume until newline

		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];

			// a line is considered to belong to this code block as long as it is intended by 4 spaces or a tab
			if (isset($line[0]) && ($line[0] === "\t" || strncmp($lines[$i], '    ', 4) === 0)) {
				$line = $line[0] === "\t" ? substr($line, 1) : substr($line, 4);
				$content[] = $line;
			// but also if it is empty and the next line is intended by 4 spaces or a tab
			} elseif ((empty($line) || rtrim($line) === '') && isset($lines[$i + 1][0]) &&
				      ($lines[$i + 1][0] === "\t" || strncmp($lines[$i + 1], '    ', 4) === 0)) {
				if (!empty($line)) {
					$line = $line[0] === "\t" ? substr($line, 1) : substr($line, 4);
				}
				$content[] = $line;
			} else {
				break;
			}
		}

		$block = [
			'code',
			'content' => implode("\n", $content),
		];
		return [$block, --$i];
	}

	/**
	 * Renders a code block
	 */
	protected function renderCode($block)
	{
		$class = isset($block['language']) ? ' class="language-' . $block['language'] . '"' : '';
		return "<pre><code$class>" . htmlspecialchars($block['content'] . "\n", ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code></pre>\n";
	}
}
