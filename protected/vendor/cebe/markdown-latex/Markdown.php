<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\latex;

use cebe\markdown\block\CodeTrait;
use cebe\markdown\block\HeadlineTrait;
use cebe\markdown\block\ListTrait;
use cebe\markdown\block\QuoteTrait;
use cebe\markdown\block\RuleTrait;

use cebe\markdown\inline\CodeTrait as InlineCodeTrait;
use cebe\markdown\inline\EmphStrongTrait;
use cebe\markdown\inline\LinkTrait;

use MikeVanRiel\TextToLatex;

/**
 * Markdown parser for the [initial markdown spec](http://daringfireball.net/projects/markdown/syntax).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Markdown extends \cebe\markdown\Parser
{
	// include block element parsing using traits
	use CodeTrait;
	use HeadlineTrait;
	use ListTrait {
		// Check Ul List before headline
		identifyUl as protected identifyBUl;
		consumeUl as protected consumeBUl;
	}
	use QuoteTrait;
	use RuleTrait {
		// Check Hr before checking lists
		identifyHr as protected identifyAHr;
		consumeHr as protected consumeAHr;
	}

	// include inline element parsing using traits
	use InlineCodeTrait;
	use EmphStrongTrait;
	use LinkTrait;

	/**
	 * @var string this string will be prefixed to all auto generated labels.
	 * This can be used to disambiguate labels when combining multiple markdown files into one document.
	 */
	public $labelPrefix = '';

	/**
	 * @var array these are "escapeable" characters. When using one of these prefixed with a
	 * backslash, the character will be outputted without the backslash and is not interpreted
	 * as markdown.
	 */
	protected $escapeCharacters = [
		'\\', // backslash
		'`', // backtick
		'*', // asterisk
		'_', // underscore
		'{', '}', // curly braces
		'[', ']', // square brackets
		'(', ')', // parentheses
		'#', // hash mark
		'+', // plus sign
		'-', // minus sign (hyphen)
		'.', // dot
		'!', // exclamation mark
		'<', '>',
	];


	/**
	 * @inheritDoc
	 */
	protected function prepare()
	{
		// reset references
		$this->references = [];
	}

	/**
	 * Consume lines for a paragraph
	 *
	 * Allow headlines and code to break paragraphs
	 */
	protected function consumeParagraph($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (!empty($line) && ltrim($line) !== '' &&
				!($line[0] === "\t" || $line[0] === " " && strncmp($line, '    ', 4) === 0) &&
				!$this->identifyHeadline($line, $lines, $i))
			{
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'paragraph',
			'content' => $this->parseInline(implode("\n", $content)),
		];
		return [$block, --$i];
	}


	// rendering adjusted for LaTeX output


	/**
	 * @inheritdoc
	 */
	protected function renderParagraph($block)
	{
		return $this->renderAbsy($block['content']) . "\n\n";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderQuote($block)
	{
		return '\begin{quote}' . $this->renderAbsy($block['content']) . "\\end{quote}\n";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderCode($block)
	{
		$language = isset($block['language']) ? "\\lstset{language={$block['language']}}" : '\lstset{language={}}';
		return "$language\\begin{lstlisting}\n{$block['content']}\n\\end{lstlisting}\n";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderList($block)
	{
		$type = ($block['list'] === 'ol') ? 'enumerate' : 'itemize';
		$output = "\\begin{{$type}}\n";

		foreach ($block['items'] as $item => $itemLines) {
			$output .= '\item ' . $this->renderAbsy($itemLines). "\n";
		}

		return "$output\\end{{$type}}\n";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderHeadline($block)
	{
		$content = $this->renderAbsy($block['content']);
		switch($block['level']) {
			case 1: return "\\section{{$content}}\n";
			case 2: return "\\subsection{{$content}}\n";
			case 3: return "\\subsubsection{{$content}}\n";
			default: return "\\paragraph{{$content}}\n";
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function renderHr($block)
	{
		return "\n\\noindent\\rule{\\textwidth}{0.4pt}\n";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderLink($block)
	{
		if (isset($block['refkey'])) {
			if (($ref = $this->lookupReference($block['refkey'])) !== false) {
				$block = array_merge($block, $ref);
			} else {
				return $block['orig'];
			}
		}

		$url = $block['url'];
		$text = $this->renderAbsy($block['text']);
		if (strpos($url, '://') === false) {
			// consider all non absolute links as relative in the document
			// $title is ignored in this case.
			if (isset($url[0]) && $url[0] === '#') {
				$url = $this->labelPrefix . $url;
			}
			return '\hyperref['.str_replace('#', '::', $url).']{' . $text . '}';
		} else {
			return $text . '\\footnote{' . (empty($block['title']) ? '' : $this->escapeLatex($block['title']) . ': ') . '\url{' . $this->escapeUrl($url) . '}}';
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function renderImage($block)
	{
		if (isset($block['refkey'])) {
			if (($ref = $this->lookupReference($block['refkey'])) !== false) {
				$block = array_merge($block, $ref);
			} else {
				return $block['orig'];
			}
		}

		// TODO create figure with caption with title
		$url = $this->escapeUrl($block['url']);
		return "\\noindent\\includegraphics[width=\\textwidth]{{$url}}";
	}

	/**
	 * Parses <a name="..."></a> tags as reference labels
	 */
	private function parseInlineHtml($text)
	{
		if (strpos($text, '>') !== false) {
			// convert a name markers to \labels
			if (preg_match('~^<a name="(.*?)">.*?</a>~i', $text, $matches)) {
				return [
					['label', 'name' => str_replace('#', '::', $this->labelPrefix . $matches[1])],
					strlen($matches[0])
				];
			}
		}
		return [['text', '<'], 1];
	}

	/**
	 * renders a reference label
	 */
	protected function renderLabel($block)
	{
		return "\\label{{$block['name']}}";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderEmail($block)
	{
		$email = $this->escapeUrl($block[1]);
		return "\\href{mailto:{$email}}{{$email}}";
	}

	/**
	 * @inheritdoc
	 */
	protected function renderUrl($block)
	{
		return '\url{' . $this->escapeUrl($block[1]) . '}';
	}

	/**
	 * @inheritdoc
	 */
	protected function renderInlineCode($block)
	{
		if (strpos($block[1], '|') !== false) {
			return '\\lstinline`' . str_replace("\n", ' ', $block[1]) . '`'; // TODO make this more robust against code containing backticks
		} else {
			return '\\lstinline|' . str_replace("\n", ' ', $block[1]) . '|';
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function renderStrong($block)
	{
		return '\textbf{' . $this->renderAbsy($block[1]) . '}';
	}

	/**
	 * @inheritdoc
	 */
	protected function renderEmph($block)
	{
		return '\textit{' . $this->renderAbsy($block[1]) . '}';
	}

	private $_escaper;

	/**
	 * Escape special characters in URLs
	 */
	protected function escapeUrl($string)
	{
		return str_replace('%', '\\%', $this->escapeLatex($string));
	}

	/**
	 * Escape special LaTeX characters
	 */
	protected function escapeLatex($string)
	{
		if ($this->_escaper === null) {
			$this->_escaper = new TextToLatex();
		}
		return $this->_escaper->convert($string);
	}

	/**
	 * @inheritdocs
	 *
	 * Parses a newline indicated by two spaces on the end of a markdown line.
	 */
	protected function renderText($text)
	{
		return str_replace("  \n", "\\\\\n", $this->escapeLatex($text[1]));
	}
}
