<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\inline;

/**
 * Adds inline emphasizes and strong elements
 */
trait EmphStrongTrait
{
	/**
	 * Parses empathized and strong elements.
	 * @marker _
	 * @marker *
	 */
	protected function parseEmphStrong($text)
	{
		$marker = $text[0];

		if (!isset($text[1])) {
			return [['text', $text[0]], 1];
		}

		if ($marker == $text[1]) { // strong
			if ($marker == '*' && preg_match('/^[*]{2}((?:[^*]|[*][^*]*[*])+?)[*]{2}(?![*]{2})/s', $text, $matches) ||
				$marker == '_' && preg_match('/^__((?:[^_]|_[^_]*_)+?)__(?!__)/us', $text, $matches)) {

				return [
					[
						'strong',
						$this->parseInline($matches[1]),
					],
					strlen($matches[0])
				];
			}
		} else { // emph
			if ($marker == '*' && preg_match('/^[*]((?:[^*]|[*][*][^*]+?[*][*])+?)[*](?![*][^*])/s', $text, $matches) ||
				$marker == '_' && preg_match('/^_((?:[^_]|__[^_]*__)+?)_(?!_[^_])\b/us', $text, $matches)) {
				return [
					[
						'emph',
						$this->parseInline($matches[1]),
					],
					strlen($matches[0])
				];
			}
		}
		return [['text', $text[0]], 1];
	}

	protected function renderStrong($block)
	{
		return '<strong>' . $this->renderAbsy($block[1]) . '</strong>';
	}

	protected function renderEmph($block)
	{
		return '<em>' . $this->renderAbsy($block[1]) . '</em>';
	}
}
