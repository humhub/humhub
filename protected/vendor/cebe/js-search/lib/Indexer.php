<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/js-search/blob/master/LICENSE
 * @link https://github.com/cebe/js-search#readme
 */

namespace cebe\jssearch;

use cebe\jssearch\analyzer\HtmlAnalyzer;
use cebe\jssearch\tokenizer\StandardTokenizer;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Indexer
{
	public $index = [];
	public $files = [];

	public function indexFiles($files, $basePath, $baseUrl = './')
	{
		$fi = count($this->files);
		foreach($files as $file) {

			$fi++;

			$contents = file_get_contents($file);

			// create file entry
			$this->files[$fi] = $this->generateFileInfo($file, $contents, $basePath, $baseUrl);

			// analyze file
			foreach($this->getAnalyzer()->analyze($contents, $this->getTokenizer()) as $index) {
				foreach($index as $word) {
					// $word['t'] - the token
					// $word['w'] - the weight
					if (isset($this->index[$word['t']][$fi])) {
						$this->index[$word['t']][$fi]['w'] *= $word['w'];
					} else {
						$this->index[$word['t']][$fi] = [
							'f' => $fi,
							'w' => $word['w'],
						];
					}

				}
			}
		}

		// reset array indexes for files to create correct json arrays
		foreach($this->index as $word => $files) {
			$this->index[$word] = array_values($files);
		}
	}

	protected function generateFileInfo($file, $contents, $basePath, $baseUrl)
	{
		// create file entry
		if (preg_match('~<h1>(.*?)</h1>~s', $contents, $matches)) {
			$title = strip_tags($matches[1]);
		} elseif (preg_match('~<title>(.*?)</title>~s', $contents, $matches)) {
			$title = strip_tags($matches[1]);
		} else {
			$title = '<i>No title</i>';
		}
		return [
			'url' => $baseUrl . str_replace('\\', '/', substr($file, strlen(rtrim($basePath, '\\/')))),
			'title' => $title,
		];
	}

	public function exportJs()
	{
		$index = json_encode($this->index);
		$files = json_encode($this->files);
		$tokenizeString = $this->getTokenizer()->tokenizeJs();

		return <<<JS
jssearch.index = $index;
jssearch.files = $files;
jssearch.tokenizeString = $tokenizeString;
JS;
	}

	private $_tokenizer;

	/**
	 * @return TokenizerInterface
	 */
	public function getTokenizer()
	{
		if ($this->_tokenizer === null) {
			$this->_tokenizer = new StandardTokenizer();
		}
		return $this->_tokenizer;
	}

	/**
	 * @param TokenizerInterface $tokenizer
	 */
	public function setTokenizer($tokenizer)
	{
		$this->_tokenizer = $tokenizer;
	}

	private $_analyzer;

	/**
	 * @return AnalyzerInterface
	 */
	public function getAnalyzer()
	{
		if ($this->_analyzer === null) {
			$this->_analyzer = new HtmlAnalyzer();
		}
		return $this->_analyzer;
	}

	/**
	 * @param AnalyzerInterface $analyzer
	 */
	public function setAnalyzer($analyzer)
	{
		$this->_analyzer = $analyzer;
	}
}