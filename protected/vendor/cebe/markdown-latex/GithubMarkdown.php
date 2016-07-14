<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\latex;

use cebe\markdown\block\FencedCodeTrait;
use cebe\markdown\block\TableTrait;
use cebe\markdown\inline\StrikeoutTrait;
use cebe\markdown\inline\UrlLinkTrait;

/**
 * Markdown parser for github flavored markdown.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class GithubMarkdown extends Markdown
{
	// include block element parsing using traits
	use TableTrait;
	use FencedCodeTrait;

	// include inline element parsing using traits
	use StrikeoutTrait;
	use UrlLinkTrait;

	/**
	 * @var boolean whether to interpret newlines as `<br />`-tags.
	 * This feature is useful for comments where newlines are often meant to be real new lines.
	 */
	public $enableNewlines = false;

	/**
	 * @inheritDoc
	 */
	protected $escapeCharacters = [
		// from Markdown
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
		// added by GithubMarkdown
		':', // colon
		'|', // pipe
	];


	/**
	 * @inheritdoc
	 */
	protected function renderCode($block)
	{
		// make sure this is not replaced by the trait
		return parent::renderCode($block);
	}

	/**
	 * @inheritdoc
	 */
	protected function renderAutoUrl($block)
	{
		return '\url{' . $this->escapeUrl($block[1]) . '}';
	}

	/**
	 * @inheritdoc
	 */
	protected function renderStrike($block)
	{
		return '\sout{' . $this->renderAbsy($block[1]) . '}';
	}

	/**
	 * @inheritdocs
	 *
	 * Parses a newline indicated by two spaces on the end of a markdown line.
	 */
	protected function renderText($text)
	{
		if ($this->enableNewlines) {
			return preg_replace("/(  \n|\n)/", "\\\\\\\\\n", $this->escapeLatex($text[1]));
		} else {
			return parent::renderText($text);
		}
	}

	private $_tableCellHead = false;

	protected function renderTable($block)
	{
		$align = [];
		foreach($block['cols'] as $col) {
			if (empty($col)) {
				$align[] = 'l';
			} else {
				$align[] = $col[0];
			}
		}
		$align = implode('|', $align);

		$content = '';
		$first = true;
		foreach($block['rows'] as $row) {
			$this->_tableCellHead = $first;
			$content .= $this->renderAbsy($this->parseInline($row)) . "\\\\ \\hline\n"; // TODO move this to the consume step
			$first = false;
		}
		return "\n\\noindent\\begin{tabular}{|$align|}\\hline\n$content\\end{tabular}\n";
	}

	/**
	 * @marker |
	 */
	protected function parseTd($markdown)
	{
		if (isset($this->context[1]) && $this->context[1] === 'table') {
			return [['tableSep'], 1];
		}
		return [['text', $markdown[0]], 1];
	}

	protected function renderTableSep($block)
	{
		return '&';
	}
}
