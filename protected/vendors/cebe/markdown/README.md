A super fast, highly extensible markdown parser for PHP
=======================================================

[![Latest Stable Version](https://poser.pugx.org/cebe/markdown/v/stable.png)](https://packagist.org/packages/cebe/markdown)
[![Total Downloads](https://poser.pugx.org/cebe/markdown/downloads.png)](https://packagist.org/packages/cebe/markdown)
[![Build Status](https://travis-ci.org/cebe/markdown.svg?branch=master)](http://travis-ci.org/cebe/markdown)
[![Tested against HHVM](http://hhvm.h4cc.de/badge/cebe/markdown.png)](http://hhvm.h4cc.de/package/cebe/markdown)
[![Code Coverage](https://scrutinizer-ci.com/g/cebe/markdown/badges/coverage.png?s=db6af342d55bea649307ef311fbd536abb9bab76)](https://scrutinizer-ci.com/g/cebe/markdown/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/cebe/markdown/badges/quality-score.png?s=17448ca4d140429fd687c58ff747baeb6568d528)](https://scrutinizer-ci.com/g/cebe/markdown/)

What is this? <a name="what"></a>
-------------

A set of [PHP][] classes, each representing a [Markdown][] flavor, and a command line tool
for converting markdown files to HTML files.

The implementation focus is to be **fast** (see [benchmark][]) and **extensible**.
Parsing Markdown to HTML is as simple as calling a single method (see [Usage](#usage)) providing a solid implementation
that gives most expected results even in non-trivial edge cases.

Extending the Markdown language with new elements is as simple as adding a new method to the class that converts the
markdown text to the expected output in HTML. This is possible without dealing with complex and error prone regular expressions.
It is also possible to hook into the markdown structure and add elements or read meta information using the internal representation
of the Markdown text as an abstract syntax tree (see [Extending the language](#extend)).

Currently the following markdown flavors are supported:

- **Traditional Markdown** according to <http://daringfireball.net/projects/markdown/syntax> ([try it!](http://markdown.cebe.cc/try?flavor=default)).
- **Github flavored Markdown** according to <https://help.github.com/articles/github-flavored-markdown> ([try it!](http://markdown.cebe.cc/try?flavor=gfm)).
- **Markdown Extra** according to <http://michelf.ca/projects/php-markdown/extra/> (currently not fully supported WIP see [#25][], [try it!](http://markdown.cebe.cc/try?flavor=extra))
- Any mixed Markdown flavor you like because of its highly extensible structure (See documentation below).

Future plans are to support:

- Smarty Pants <http://daringfireball.net/projects/smartypants/>
- ... (Feel free to [suggest](https://github.com/cebe/markdown/issues/new) further additions!)

[PHP]: http://php.net/ "PHP is a popular general-purpose scripting language that is especially suited to web development."
[Markdown]: http://en.wikipedia.org/wiki/Markdown "Markdown on Wikipedia"
[#25]: https://github.com/cebe/markdown/issues/25 "issue #25"
[benchmark]: https://github.com/kzykhys/Markbench#readme "kzykhys/Markbench on github"

### Who is using it?

- It powers the [API-docs and the definitive guide](http://www.yiiframework.com/doc-2.0/) for the [Yii Framework][] [2.0](https://github.com/yiisoft/yii2).

[Yii Framework]: http://www.yiiframework.com/ "The Yii PHP Framework"


Installation <a name="installation"></a>
------------

[PHP 5.4 or higher](http://www.php.net/downloads.php) is required to use it.
It will also run on facebook's [hhvm](http://hhvm.com/).

Installation is recommended to be done via [composer][] by running:

	composer require cebe/markdown "~1.0.1"

Alternatively you can add the following to the `require` section in your `composer.json` manually:

```json
"cebe/markdown": "~1.0.1"
```

Run `composer update` afterwards.

[composer]: https://getcomposer.org/ "The PHP package manager"


Usage <a name="usage"></a>
-----

### In your PHP project

To parse your markdown you need only two lines of code. The first one is to choose the markdown flavor as
one of the following:

- Traditional Markdown: `$parser = new \cebe\markdown\Markdown();`
- Github Flavored Markdown: `$parser = new \cebe\markdown\GithubMarkdown();`
- Markdown Extra: `$parser = new \cebe\markdown\MarkdownExtra();`

The next step is to call the `parse()`-method for parsing the text using the full markdown language
or calling the `parseParagraph()`-method to parse only inline elements.

Here are some examples:

```php
// traditional markdown and parse full text
$parser = new \cebe\markdown\Markdown();
$parser->parse($markdown);

// use github markdown
$parser = new \cebe\markdown\GithubMarkdown();
$parser->parse($markdown);

// use markdown extra
$parser = new \cebe\markdown\MarkdownExtra();
$parser->parse($markdown);

// parse only inline elements (useful for one-line descriptions)
$parser = new \cebe\markdown\GithubMarkdown();
$parser->parseParagraph($markdown);
```

You may optionally set one of the following options on the parser object:

For all Markdown Flavors:

- `$parser->html5 = true` to enable HTML5 output instead of HTML4.
- `$parser->keepListStartNumber = true` to enable keeping the numbers of ordered lists as specified in the markdown.
  The default behavior is to always start from 1 and increment by one regardless of the number in markdown.

For GithubMarkdown:

- `$parser->enableNewlines = true` to convert all newlines to `<br/>`-tags. By default only newlines with two preceding spaces are converted to `<br/>`-tags. 

It is recommended to use UTF-8 encoding for the input strings. Other encodings are currently not tested.

### The command line script

You can use it to render this readme:

    bin/markdown README.md > README.html

Using github flavored markdown:

    bin/markdown --flavor=gfm README.md > README.html

or convert the original markdown description to html using the unix pipe:

    curl http://daringfireball.net/projects/markdown/syntax.text | bin/markdown > md.html

Here is the full Help output you will see when running `bin/markdown --help`:

    PHP Markdown to HTML converter
    ------------------------------
    
    by Carsten Brandt <mail@cebe.cc>
    
    Usage:
        bin/markdown [--flavor=<flavor>] [--full] [file.md]
    
        --flavor  specifies the markdown flavor to use. If omitted the original markdown by John Gruber [1] will be used.
                  Available flavors:
    
                  gfm   - Github flavored markdown [2]
                  extra - Markdown Extra [3]

        --full    ouput a full HTML page with head and body. If not given, only the parsed markdown will be output.

        --help    shows this usage information.

        If no file is specified input will be read from STDIN.

    Examples:

        Render a file with original markdown:

            bin/markdown README.md > README.html

        Render a file using gihtub flavored markdown:

            bin/markdown --flavor=gfm README.md > README.html

        Convert the original markdown description to html using STDIN:

            curl http://daringfireball.net/projects/markdown/syntax.text | bin/markdown > md.html

    
    [1] http://daringfireball.net/projects/markdown/syntax
    [2] https://help.github.com/articles/github-flavored-markdown
    [3] http://michelf.ca/projects/php-markdown/extra/


Extensions
----------

Here are some extensions to this library:

- [Bogardo/markdown-codepen](https://github.com/Bogardo/markdown-codepen) - shortcode to embed codepens from http://codepen.io/ in markdown.
- [kartik-v/yii2-markdown](https://github.com/kartik-v/yii2-markdown) - Advanced Markdown editing and conversion utilities for Yii Framework 2.0.
- [cebe/markdown-latex](https://github.com/cebe/markdown-latex) - Convert Markdown to LaTeX and PDF
- ... [add yours!](https://github.com/cebe/markdown/edit/master/README.md#L98)


Extending the language <a name="extend"></a>
----------------------

Markdown consists of two types of language elements, I'll call them block and inline elements simlar to what you have in
HTML with `<div>` and `<span>`. Block elements are normally spreads over several lines and are separated by blank lines.
The most basic block element is a paragraph (`<p>`).
Inline elements are elements that are added inside of block elements i.e. inside of text.

This markdown parser allows you to extend the markdown language by changing existing elements behavior and also adding
new block and inline elements. You do this by extending from the parser class and adding/overriding class methods and
properties. For the different element types there are different ways to extend them as you will see in the following sections.

### Adding block elements

The markdown is parsed line by line to identify each non-empty line as one of the block element types.
To identify a line as the beginning of a block element it calls all protected class methods who's name begins with `identify`.
An identify function returns true if it has identified the block element it is responsible for or false if not.
In the following example we will implement support for [fenced code blocks][] which are part of the github flavored markdown.

[fenced code blocks]: https://help.github.com/articles/github-flavored-markdown#fenced-code-blocks
                      "Fenced code block feature of github flavored markdown"

```php
<?php

class MyMarkdown extends \cebe\markdown\Markdown
{
	protected function identifyLine($line, $lines, $current)
	{
		// if a line starts with at least 3 backticks it is identified as a fenced code block
		if (strncmp($line, '```', 3) === 0) {
			return 'fencedCode';
		}
		return parent::identifyLine($lines, $current);
	}

	// ...
}
```

In the above, `$line` is a string containing the content of the current line and is equal to `$lines[$current]`.
You may use `$lines` and `$current` to check other lines than the current line. In most cases you can ignore these parameters.

Parsing of a block element is done in two steps:

1. "consuming" all the lines belonging to it. In most cases this is iterating over the lines starting from the identified
   line until a blank line occurs. This step is implemented by a method named `consume{blockName}()` where `{blockName}`
   is the same name as used for the identify function above. The consume method also takes the lines array
   and the number of the current line. It will return two arguments: an array representing the block element in the abstract syntax tree
   of the markdown document and the line number to parse next. In the abstract syntax array the first element refers to the name of
   the element, all other array elements can be freely defined by yourself.
   In our example we will implement it like this:

   ```php
	protected function consumeFencedCode($lines, $current)
	{
		// create block array
		$block = [
			'fencedCode',
			'content' => [],
		];
		$line = rtrim($lines[$current]);

		// detect language and fence length (can be more than 3 backticks)
		$fence = substr($line, 0, $pos = strrpos($line, '`') + 1);
		$language = substr($line, $pos);
		if (!empty($language)) {
			$block['language'] = $language;
		}

		// consume all lines until ```
		for($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			if (rtrim($line = $lines[$i]) !== $fence) {
				$block['content'][] = $line;
			} else {
				// stop consuming when code block is over
				break;
			}
		}
		return [$block, $i];
	}
	```

2. "rendering" the element. After all blocks have been consumed, they are being rendered using the
   `render{elementName}()`-method where `elementName` refers to the name of the element in the abstract syntax tree:

   ```php
	protected function renderFencedCode($block)
	{
		$class = isset($block['language']) ? ' class="language-' . $block['language'] . '"' : '';
		return "<pre><code$class>" . htmlspecialchars(implode("\n", $block['content']) . "\n", ENT_NOQUOTES, 'UTF-8') . '</code></pre>';
	}
   ```

   You may also add code highlighting here. In general it would also be possible to render ouput in a different language than
   HTML for example LaTeX.


### Adding inline elements

Adding inline elements is different from block elements as they are parsed using markers in the text.
An inline element is identified by a marker that marks the beginning of an inline element (e.g. `[` will mark a possible
beginning of a link or `` ` `` will mark inline code).

Parsing methods for inline elements are also protected and identified by the prefix `parse`. Additionally a `@marker` annotation
in PHPDoc is needed to register the parse function for one or multiple markers.
The method will then be called when a marker is found in the text. As an argument it takes the text starting at the position of the marker.
The parser method will return an array containing the element of the abstract sytnax tree and an offset of text it has
parsed from the input markdown. All text up to this offset will be removed from the markdown before the next marker will be searched.

As an example, we will add support for the [strikethrough][] feature of github flavored markdown:

[strikethrough]: https://help.github.com/articles/github-flavored-markdown#strikethrough "Strikethrough feature of github flavored markdown"

```php
<?php

class MyMarkdown extends \cebe\markdown\Markdown
{
	/**
	 * @marker ~~
	 */
	protected function parseStrike($markdown)
	{
		// check whether the marker really represents a strikethrough (i.e. there is a closing ~~)
		if (preg_match('/^~~(.+?)~~/', $markdown, $matches)) {
			return [
			    // return the parsed tag as an element of the abstract syntax tree and call `parseInline()` to allow
			    // other inline markdown elements inside this tag
				['strike', $this->parseInline($matches[1])],
				// return the offset of the parsed text
				strlen($matches[0])
			];
		}
		// in case we did not find a closing ~~ we just return the marker and skip 2 characters
		return [['text', '~~'], 2];
	}

	// rendering is the same as for block elements, we turn the abstract syntax array into a string.
	protected function renderStrike($element)
	{
		return '<del>' . $this->renderAbsy($element[1]) . '</del>';
	}
}
```

### Composing your own Markdown flavor

TBD


Acknowledgements <a name="ack"></a>
----------------

I'd like to thank [@erusev][] for creating [Parsedown][] which heavily influenced this work and provided
the idea of the line based parsing approach.

[@erusev]: https://github.com/erusev "Emanuil Rusev"
[Parsedown]: http://parsedown.org/ "The Parsedown PHP Markdown parser"

FAQ <a name="faq"></a>
---

### Why another markdown parser?

While reviewing PHP markdown parsers for choosing one to use bundled with the [Yii framework 2.0][]
I found that most of the implementations use regex to replace patterns instead
of doing real parsing. This way extending them with new language elements is quite hard
as you have to come up with a complex regex, that matches your addition but does not mess
with other elements. Such additions are very common as you see on github which supports referencing
issues, users and commits in the comments.
A [real parser][] should use context aware methods that walk trough the text and
parse the tokens as they find them. The only implentation that I have found that uses
this approach is [Parsedown][] which also shows that this implementation is [much faster][benchmark]
than the regex way. Parsedown however is an implementation that focuses on speed and implements
its own flavor (mainly github flavored markdown) in one class and at the time of this writing was
not easily extensible.

Given the situation above I decided to start my own implementation using the parsing approach
from Parsedown and making it extensible creating a class for each markdown flavor that extend each
other in the way that also the markdown languages extend each other.
This allows you to choose between markdown language flavors and also provides a way to compose your
own flavor picking the best things from all.
I chose this approach as it is easier to implement and also more intuitive approach compared
to using callbacks to inject functionallity into the parser.

[real parser]: http://en.wikipedia.org/wiki/Parsing#Types_of_parser

[Parsedown]: http://parsedown.org/ "The Parsedown PHP Markdown parser"

### Where do I report bugs or rendering issues?

Just [open an issue][] on github, post your markdown code and describe the problem. You may also attach screenshots of the rendered HTML result to describe your problem.

[open an issue]: https://github.com/cebe/markdown/issues/new

### How can I contribute to this library?

Check the [CONTRIBUTING.md](CONTRIBUTING.md) file for more info.


### Am I free to use this?

This library is open source and licensed under the [MIT License][]. This means that you can do whatever you want
with it as long as you mention my name and include the [license file][license]. Check the [license][] for details.

[MIT License]: http://opensource.org/licenses/MIT

[license]: https://github.com/cebe/markdown/blob/master/LICENSE

Contact
-------

Feel free to contact me using [email](mailto:mail@cebe.cc) or [twitter](https://twitter.com/cebe_cc).
