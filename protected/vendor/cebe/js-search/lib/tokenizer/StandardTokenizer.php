<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/js-search/blob/master/LICENSE
 * @link https://github.com/cebe/js-search#readme
 */

namespace cebe\jssearch\tokenizer;

use cebe\jssearch\TokenizerInterface;

/**
 * StandardTokenizer
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class StandardTokenizer implements TokenizerInterface
{
	/**
	 * @var array a list of stopwords to remove from the token list.
	 */
	public $stopWords = [
		// default lucene http://stackoverflow.com/questions/17527741/what-is-the-default-list-of-stopwords-used-in-lucenes-stopfilter
		"a", "an", "and", "are", "as", "at", "be", "but", "by",
		"for", "if", "in", "into", "is", "it",
		"no", "not", "of", "on", "or", "such",
		"that", "the", "their", "then", "there", "these",
		"they", "this", "to", "was", "will", "with"
	];
	/**
	 * @var string a list of characters that should be used as word delimiters.
	 */
	public $delimiters = '.,;:\\/[](){}';


	/**
	 * Tokenizes a string and returns an array of the following format:
	 *
	 * ```
	 * [['t' => 'word', 'w' => 2], ['t' => 'other', 'w' => 1]]
	 * ```
	 *
	 * where the first part is the token string and the second is a weight value.
	 *
	 * Also removes [[stopWords]] from the list.
	 *
	 * @param string $string the string to tokenize
	 * @return array
	 */
	public function tokenize($string)
	{
		$delimiters = preg_quote($this->delimiters, '/');
		return array_map(function($token) {return ['t' => $token, 'w' => 1]; }, array_filter(
			array_map(function($t) { return mb_strtolower($t, 'UTF-8'); }, preg_split("/[\\s$delimiters]+/", $string, -1, PREG_SPLIT_NO_EMPTY)),
			function($word) {
				return !in_array($word, $this->stopWords);
			}
		));
	}

	/**
	 * Returns a javascript equivalent of [[tokenize]] that will be used
	 * on client side to tokenize the search query.
	 *
	 * This is used to ensure the same tokenizer is used for building the index and for searching.
	 *
	 * @return string
	 */
	public function tokenizeJs()
	{
		$delimiters = preg_quote($this->delimiters, '/');
		$stopWords = json_encode($this->stopWords);
		return <<<JS
function(string) {
		var stopWords = $stopWords;
		return string.split(/[\s$delimiters]+/).map(function(val) {
			return val.toLowerCase();
		}).filter(function(val) {
			return !(val in stopWords);
		}).map(function(word) {
			return {t: word, w: 1};
		});
}
JS;

	}
}