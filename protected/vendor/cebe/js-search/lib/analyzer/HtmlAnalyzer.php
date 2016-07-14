<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/js-search/blob/master/LICENSE
 * @link https://github.com/cebe/js-search#readme
 */

namespace cebe\jssearch\analyzer;

use cebe\jssearch\AnalyzerInterface;
use cebe\jssearch\TokenizerInterface;

/**
 * Analyzer for HTML files
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class HtmlAnalyzer implements AnalyzerInterface
{
	public $headWeight = 20;
	public $titleWeight = 4;
	public $textWeight = 1.2;

	/**
	 * @inheritDoc
	 */
	public function analyze($string, TokenizerInterface $tokenizer)
	{
		$index = array_merge(
			$this->findText($string, '~<h(\d)>(.*?)</h\1>~s',   ['text' => 2, 'weight' => 1], $tokenizer, function($w, $h) { return $w * ($this->headWeight - $h) / 10; }),
			$this->findText($string, '~<title>(.*?)</title>~s', ['text' => 1], $tokenizer, $this->titleWeight),
			$this->findText($string, '~<p>(.*?|(?R))</p>~s',    ['text' => 1], $tokenizer, $this->textWeight),
			$this->findText($string, '~<(th|td|li|dd|dt)>(.*?)</\1>~s', ['text' => 2], $tokenizer, $this->textWeight)
		);

		$wordCount = array_reduce($index, function($carry, $item) { return $carry + count($item); }, 0);
		foreach($index as $i => $words) {
			foreach($words as $w => $word) {
//				$index[$i][$w]['w'] = 1 + $index[$i][$w]['w'] / $wordCount; // TODO improve weight formula here
			}
		}
		return $index;
	}

	/**
	 * @param $string
	 * @param $pattern
	 * @param $selectors
	 * @param TokenizerInterface $tokenizer
	 */
	private function findText($string, $pattern, $selectors, $tokenizer, $weight)
	{
		$index = [];
		preg_match_all($pattern, $string, $matches);
		foreach($matches[0] as $i => $match) {
			$index[] = array_map(
				function($token) use ($weight, $matches, $selectors, $i) {
					if ($weight instanceof \Closure) {
						$w = call_user_func_array($weight, [$token['w'], $matches[$selectors['weight']][$i]]);
					} else {
						$w = $token['w'] * $weight;
					}
					return ['t' => $token['t'], 'w' => $w];
				},
				$tokenizer->tokenize(strip_tags($matches[$selectors['text']][$i]))
			);
		}
		return $index;
	}
}