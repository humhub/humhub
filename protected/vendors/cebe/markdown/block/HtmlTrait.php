<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\block;

/**
 * Adds inline and block HTML support
 */
trait HtmlTrait
{
	/**
	 * @var array HTML elements considered as inline elements.
	 * @see http://www.w3.org/wiki/HTML/Elements#Text-level_semantics
	 */
	protected $inlineHtmlElements = [
		'a', 'abbr', 'acronym',
		'b', 'basefont', 'bdo', 'big', 'br', 'button', 'blink',
		'cite', 'code',
		'del', 'dfn',
		'em',
		'font',
		'i', 'img', 'ins', 'input', 'iframe',
		'kbd',
		'label', 'listing',
		'map', 'mark',
		'nobr',
		'object',
		'q',
		'rp', 'rt', 'ruby',
		's', 'samp', 'script', 'select', 'small', 'spacer', 'span', 'strong', 'sub', 'sup',
		'tt', 'var',
		'u',
		'wbr',
		'time',
	];
	/**
	 * @var array HTML elements known to be self-closing.
	 */
	protected $selfClosingHtmlElements = [
		'br', 'hr', 'img', 'input', 'nobr',
	];

	/**
	 * identify a line as the beginning of a HTML block.
	 */
	protected function identifyHtml($line, $lines, $current)
	{
		if ($line[0] !== '<' || isset($line[1]) && $line[1] == ' ') {
			return false; // no html tag
		}

		if (strncmp($line, '<!--', 4) === 0) {
			return true; // a html comment
		}

		$gtPos = strpos($lines[$current], '>');
		$spacePos = strpos($lines[$current], ' ');
		if ($gtPos === false && $spacePos === false) {
			return false; // no html tag
		} elseif ($spacePos === false) {
			$tag = rtrim(substr($line, 1, $gtPos - 1), '/');
		} else {
			$tag = rtrim(substr($line, 1, min($gtPos, $spacePos) - 1), '/');
		}

		if (!ctype_alnum($tag) || in_array(strtolower($tag), $this->inlineHtmlElements)) {
			return false; // no html tag or inline html tag
		}
		return true;
	}

	/**
	 * Consume lines for an HTML block
	 */
	protected function consumeHtml($lines, $current)
	{
		$content = [];
		if (strncmp($lines[$current], '<!--', 4) === 0) { // html comment
			for ($i = $current, $count = count($lines); $i < $count; $i++) {
				$line = $lines[$i];
				$content[] = $line;
				if (strpos($line, '-->') !== false) {
					break;
				}
			}
		} else {
			$tag = rtrim(substr($lines[$current], 1, min(strpos($lines[$current], '>'), strpos($lines[$current] . ' ', ' ')) - 1), '/');
			$level = 0;
			if (in_array($tag, $this->selfClosingHtmlElements)) {
				$level--;
			}
			for ($i = $current, $count = count($lines); $i < $count; $i++) {
				$line = $lines[$i];
				$content[] = $line;
				$level += substr_count($line, "<$tag") - substr_count($line, "</$tag>");
				if ($level <= 0) {
					break;
				}
			}
		}
		$block = [
			'html',
			'content' => implode("\n", $content),
		];
		return [$block, $i];
	}

	/**
	 * Renders an HTML block
	 */
	protected function renderHtml($block)
	{
		return $block['content'] . "\n";
	}

	/**
	 * Parses an & or a html entity definition.
	 * @marker &
	 */
	protected function parseEntity($text)
	{
		// html entities e.g. &copy; &#169; &#x00A9;
		if (preg_match('/^&#?[\w\d]+;/', $text, $matches)) {
			return [['inlineHtml', $matches[0]], strlen($matches[0])];
		} else {
			return [['text', '&amp;'], 1];
		}
	}

	/**
	 * renders a html entity.
	 */
	protected function renderInlineHtml($block)
	{
		return $block[1];
	}

	/**
	 * Parses inline HTML.
	 * @marker <
	 */
	protected function parseInlineHtml($text)
	{
		if (strpos($text, '>') !== false) {
			if (preg_match('~^</?(\w+\d?)( .*?)?>~', $text, $matches)) {
				// HTML tags
				return [['inlineHtml', $matches[0]], strlen($matches[0])];
			} elseif (preg_match('~^<!--.*?-->~', $text, $matches)) {
				// HTML comments
				return [['inlineHtml', $matches[0]], strlen($matches[0])];
			}
		}
		return [['text', '&lt;'], 1];
	}

	/**
	 * Escapes `>` characters.
	 * @marker >
	 */
	protected function parseGt($text)
	{
		return [['text', '&gt;'], 1];
	}
}
