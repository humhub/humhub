<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\block;

/**
 * Adds the table blocks
 */
trait TableTrait
{
	private $_tableCellTag = 'td';
	private $_tableCellCount = 0;
	private $_tableCellAlign = [];

	/**
	 * identify a line as the beginning of a table block.
	 */
	protected function identifyTable($line, $lines, $current)
	{
		return strpos($line, '|') !== false && preg_match('~|.*|~', $line) && isset($lines[$current + 1]) && preg_match('~^[\s\|\:-]+$~', $lines[$current + 1]);
	}

	/**
	 * Consume lines for a table
	 */
	protected function consumeTable($lines, $current)
	{
		// consume until newline

		$block = [
			'table',
			'cols' => [],
			'rows' => [],
		];
		$beginsWithPipe = $lines[$current][0] === '|';
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];

			if ($i == $current+1) { // skip second line
				$cols = explode('|', trim($line, ' |'));
				foreach($cols as $col) {
					$col = trim($col);
					if (empty($col)) {
						$block['cols'][] = '';
						continue;
					}
					$l = ($col[0] === ':');
					$r = (substr($col, -1, 1) === ':');
					if ($l && $r) {
						$block['cols'][] = 'center';
					} elseif ($l) {
						$block['cols'][] = 'left';
					} elseif ($r) {
						$block['cols'][] = 'right';
					} else {
						$block['cols'][] = '';
					}
				}

				continue;
			}
			if (trim($line) === '' || $beginsWithPipe && $line[0] !== '|') {
				break;
			}
			if (substr($line, -2, 2) !== '\\|' || substr($line, -3, 3) === '\\\\|') {
				$block['rows'][] = trim($line, '| ');
			} else {
				$block['rows'][] = ltrim($line, '| ');
			}
		}

		return [$block, --$i];
	}

	/**
	 * render a table block
	 */
	protected function renderTable($block)
	{
		$content = '';
		$this->_tableCellAlign = $block['cols'];
		$content .= "<thead>\n";
		$first = true;
		foreach($block['rows'] as $row) {
			$this->_tableCellTag = $first ? 'th' : 'td';
			$align = empty($this->_tableCellAlign[$this->_tableCellCount]) ? '' : ' align="' . $this->_tableCellAlign[$this->_tableCellCount++] . '"';
			$tds = "<$this->_tableCellTag$align>" . $this->renderAbsy($this->parseInline($row)) . "</$this->_tableCellTag>"; // TODO move this to the consume step
			$content .= "<tr>$tds</tr>\n";
			if ($first) {
				$content .= "</thead>\n<tbody>\n";
			}
			$first = false;
			$this->_tableCellCount = 0;
		}
		return "<table>\n$content</tbody>\n</table>\n";
	}

	/**
	 * @marker |
	 */
	protected function parseTd($markdown)
	{
		if (isset($this->context[1]) && $this->context[1] === 'table') {
			$align = empty($this->_tableCellAlign[$this->_tableCellCount]) ? '' : ' align="' . $this->_tableCellAlign[$this->_tableCellCount++] . '"';
			return [['text', "</$this->_tableCellTag><$this->_tableCellTag$align>"], isset($markdown[1]) && $markdown[1] === ' ' ? 2 : 1]; // TODO make a absy node
		}
		return [['text', $markdown[0]], 1];
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
