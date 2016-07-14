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
interface TokenizerInterface
{
	/**
	 * Tokenizes a string and returns an array of the following format:
	 *
	 * ```
	 * [['word', 2], ['other', 1]]
	 * ```
	 *
	 * where the first part is the token string and the second is a weight value.
	 *
	 * @param string $string the string to tokenize
	 * @return array
	 */
	public function tokenize($string);

	/**
	 * Returns a javascript equivalent of [[tokenize]] that will be used
	 * on client side to tokenize the search query.
	 * @return string
	 */
	public function tokenizeJs();
} 