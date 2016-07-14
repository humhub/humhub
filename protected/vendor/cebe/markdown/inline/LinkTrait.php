<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\inline;

// work around https://github.com/facebook/hhvm/issues/1120
defined('ENT_HTML401') || define('ENT_HTML401', 0);

/**
 * Addes links and images as well as url markers.
 *
 * This trait conflicts with the HtmlTrait. If both are used together,
 * you have to define a resolution, by defining the HtmlTrait::parseInlineHtml
 * as private so it is not used directly:
 *
 * ```php
 * use block\HtmlTrait {
 *     parseInlineHtml as private parseInlineHtml;
 * }
 * ```
 *
 * If the method exists it is called internally by this trait.
 *
 * Also make sure to reset references on prepare():
 *
 * ```php
 * protected function prepare()
 * {
 *     // reset references
 *     $this->references = [];
 * }
 * ```
 */
trait LinkTrait
{
	/**
	 * @var array a list of defined references in this document.
	 */
	protected $references = [];

	/**
	 * Parses a link indicated by `[`.
	 * @marker [
	 */
	protected function parseLink($markdown)
	{
		if (!in_array('parseLink', array_slice($this->context, 1)) && ($parts = $this->parseLinkOrImage($markdown)) !== false) {
			list($text, $url, $title, $offset, $key) = $parts;
			return [
				[
					'link',
					'text' => $this->parseInline($text),
					'url' => $url,
					'title' => $title,
					'refkey' => $key,
					'orig' => substr($markdown, 0, $offset),
				],
				$offset
			];
		} else {
			// remove all starting [ markers to avoid next one to be parsed as link
			$result = '[';
			$i = 1;
			while (isset($markdown[$i]) && $markdown[$i] == '[') {
				$result .= '[';
				$i++;
			}
			return [['text', $result], $i];
		}
	}

	/**
	 * Parses an image indicated by `![`.
	 * @marker ![
	 */
	protected function parseImage($markdown)
	{
		if (($parts = $this->parseLinkOrImage(substr($markdown, 1))) !== false) {
			list($text, $url, $title, $offset, $key) = $parts;

			return [
				[
					'image',
					'text' => $text,
					'url' => $url,
					'title' => $title,
					'refkey' => $key,
					'orig' => substr($markdown, 0, $offset + 1),
				],
				$offset + 1
			];
		} else {
			// remove all starting [ markers to avoid next one to be parsed as link
			$result = '!';
			$i = 1;
			while (isset($markdown[$i]) && $markdown[$i] == '[') {
				$result .= '[';
				$i++;
			}
			return [['text', $result], $i];
		}
	}

	protected function parseLinkOrImage($markdown)
	{
		if (strpos($markdown, ']') !== false && preg_match('/\[((?>[^\]\[]+|(?R))*)\]/', $markdown, $textMatches)) { // TODO improve bracket regex
			$text = $textMatches[1];
			$offset = strlen($textMatches[0]);
			$markdown = substr($markdown, $offset);

			$pattern = <<<REGEXP
				/(?(R) # in case of recursion match parentheses
					 \(((?>[^\s()]+)|(?R))*\)
				|      # else match a link with title
					^\((((?>[^\s()]+)|(?R))*)(\s+"(.*?)")?\)
				)/x
REGEXP;
			if (preg_match($pattern, $markdown, $refMatches)) {
				// inline link
				return [
					$text,
					isset($refMatches[2]) ? $refMatches[2] : '', // url
					empty($refMatches[5]) ? null: $refMatches[5], // title
					$offset + strlen($refMatches[0]), // offset
					null, // reference key
				];
			} elseif (preg_match('/^([ \n]?\[(.*?)\])?/s', $markdown, $refMatches)) {
				// reference style link
				if (empty($refMatches[2])) {
					$key = strtolower($text);
				} else {
					$key = strtolower($refMatches[2]);
				}
				return [
					$text,
					null, // url
					null, // title
					$offset + strlen($refMatches[0]), // offset
					$key,
				];
			}
		}
		return false;
	}

	/**
	 * Parses inline HTML.
	 * @marker <
	 */
	protected function parseLt($text)
	{
		if (strpos($text, '>') !== false) {
			if (!in_array('parseLink', $this->context)) { // do not allow links in links
				if (preg_match('/^<([^\s]*?@[^\s]*?\.\w+?)>/', $text, $matches)) {
					// email address
					return [
						['email', $matches[1]],
						strlen($matches[0])
					];
				} elseif (preg_match('/^<([a-z]{3,}:\/\/[^\s]+?)>/', $text, $matches)) {
					// URL
					return [
						['url', $matches[1]],
						strlen($matches[0])
					];
				}
			}
			// try inline HTML if it was neither a URL nor email if HtmlTrait is included.
			if (method_exists($this, 'parseInlineHtml')) {
				return $this->parseInlineHtml($text);
			}
		}
		return [['text', '&lt;'], 1];
	}

	protected function renderEmail($block)
	{
		$email = htmlspecialchars($block[1], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
		return "<a href=\"mailto:$email\">$email</a>";
	}

	protected function renderUrl($block)
	{
		$url = htmlspecialchars($block[1], ENT_COMPAT | ENT_HTML401, 'UTF-8');
		$text = htmlspecialchars(urldecode($block[1]), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
		return "<a href=\"$url\">$text</a>";
	}

	protected function lookupReference($key)
	{
		$normalizedKey = preg_replace('/\s+/', ' ', $key);
		if (isset($this->references[$key]) || isset($this->references[$key = $normalizedKey])) {
			return $this->references[$key];
		}
		return false;
	}

	protected function renderLink($block)
	{
		if (isset($block['refkey'])) {
			if (($ref = $this->lookupReference($block['refkey'])) !== false) {
				$block = array_merge($block, $ref);
			} else {
				return $block['orig'];
			}
		}
		return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
			. (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
			. '>' . $this->renderAbsy($block['text']) . '</a>';
	}

	protected function renderImage($block)
	{
		if (isset($block['refkey'])) {
			if (($ref = $this->lookupReference($block['refkey'])) !== false) {
				$block = array_merge($block, $ref);
			} else {
				return $block['orig'];
			}
		}
		return '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
			. ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
			. (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
			. ($this->html5 ? '>' : ' />');
	}

	// references

	protected function identifyReference($line)
	{
		return ($line[0] === ' ' || $line[0] === '[') && preg_match('/^ {0,3}\[(.+?)\]:\s*([^\s]+?)(?:\s+[\'"](.+?)[\'"])?\s*$/', $line);
	}

	/**
	 * Consume link references
	 */
	protected function consumeReference($lines, $current)
	{
		while (isset($lines[$current]) && preg_match('/^ {0,3}\[(.+?)\]:\s*(.+?)(?:\s+[\(\'"](.+?)[\)\'"])?\s*$/', $lines[$current], $matches)) {
			$label = strtolower($matches[1]);

			$this->references[$label] = [
				'url' => $matches[2],
			];
			if (isset($matches[3])) {
				$this->references[$label]['title'] = $matches[3];
			} else {
				// title may be on the next line
				if (isset($lines[$current + 1]) && preg_match('/^\s+[\(\'"](.+?)[\)\'"]\s*$/', $lines[$current + 1], $matches)) {
					$this->references[$label]['title'] = $matches[1];
					$current++;
				}
			}
			$current++;
		}
		return [false, --$current];
	}
}
