<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/js-search/blob/master/LICENSE
 * @link https://github.com/cebe/js-search#readme
 */

namespace cebe\jssearch;

/**
 * Interface for all Tokenizers.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
interface AnalyzerInterface
{
	/**
	 * Analyzes a string and returns an array of the following format:
	 *
	 * TODO
	 * ```
	 * ```
	 *
	 * @param string $string the string to analyze
	 * @return array
	 */
	public function analyze($string, TokenizerInterface $tokenizer);
}