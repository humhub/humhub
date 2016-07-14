<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace cebe\markdown\tests;

use cebe\markdown\Parser;

/**
 * Base class for all Test cases.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
abstract class BaseMarkdownTest extends \PHPUnit_Framework_TestCase
{
	protected $outputFileExtension = '.html';

	abstract public function getDataPaths();

	/**
	 * @return Parser
	 */
	abstract public function createMarkdown();

	/**
	 * @dataProvider dataFiles
	 */
	public function testParse($path, $file)
	{
		list($markdown, $html) = $this->getTestData($path, $file);
		// Different OS line endings should not affect test
		$html = str_replace(["\r\n", "\n\r", "\r"], "\n", $html);

		$m = $this->createMarkdown();
		$this->assertEquals($html, $m->parse($markdown));
	}

	public function testUtf8()
	{
		$this->assertSame("<p>абвгдеёжзийклмнопрстуфхцчшщъыьэюя</p>\n", $this->createMarkdown()->parse('абвгдеёжзийклмнопрстуфхцчшщъыьэюя'));
		$this->assertSame("<p>there is a charater, 配</p>\n", $this->createMarkdown()->parse('there is a charater, 配'));
		$this->assertSame("<p>Arabic Latter \"م (M)\"</p>\n", $this->createMarkdown()->parse('Arabic Latter "م (M)"'));
		$this->assertSame("<p>電腦</p>\n", $this->createMarkdown()->parse('電腦'));

		$this->assertSame('абвгдеёжзийклмнопрстуфхцчшщъыьэюя', $this->createMarkdown()->parseParagraph('абвгдеёжзийклмнопрстуфхцчшщъыьэюя'));
		$this->assertSame('there is a charater, 配', $this->createMarkdown()->parseParagraph('there is a charater, 配'));
		$this->assertSame('Arabic Latter "م (M)"', $this->createMarkdown()->parseParagraph('Arabic Latter "م (M)"'));
		$this->assertSame('電腦', $this->createMarkdown()->parseParagraph('電腦'));
	}

	public function testInvalidUtf8()
	{
		$m = $this->createMarkdown();
		$this->assertEquals("<p><code>�</code></p>\n", $m->parse("`\x80`"));
		$this->assertEquals('<code>�</code>', $m->parseParagraph("`\x80`"));
	}

	public function pregData()
	{
		// http://en.wikipedia.org/wiki/Newline#Representations
		return [
			["a\r\nb", "a\nb"],
			["a\n\rb", "a\nb"], // Acorn BBC and RISC OS spooled text output :)
			["a\nb", "a\nb"],
			["a\rb", "a\nb"],

			["a\n\nb", "a\n\nb", "a</p>\n<p>b"],
			["a\r\rb", "a\n\nb", "a</p>\n<p>b"],
			["a\n\r\n\rb", "a\n\nb", "a</p>\n<p>b"], // Acorn BBC and RISC OS spooled text output :)
			["a\r\n\r\nb", "a\n\nb", "a</p>\n<p>b"],
		];
	}

	/**
	 * @dataProvider pregData
	 */
	public function testPregReplaceR($input, $exptected, $pexpect = null)
	{
		$this->assertSame($exptected, $this->createMarkdown()->parseParagraph($input));
		$this->assertSame($pexpect === null ? "<p>$exptected</p>\n" : "<p>$pexpect</p>\n", $this->createMarkdown()->parse($input));
	}

	public function getTestData($path, $file)
	{
		return [
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . '.md'),
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . $this->outputFileExtension),
		];
	}

	public function dataFiles()
	{
		$files = [];
		foreach ($this->getDataPaths() as $name => $src) {
			$handle = opendir($src);
			if ($handle === false) {
				throw new \Exception('Unable to open directory: ' . $src);
			}
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}

				if (substr($file, -3, 3) === '.md' && file_exists($src . '/' . substr($file, 0, -3) .  $this->outputFileExtension)) {
					$files[] = [$name, substr($file, 0, -3)];
				}
			}
			closedir($handle);
		}
		return $files;
	}
}
