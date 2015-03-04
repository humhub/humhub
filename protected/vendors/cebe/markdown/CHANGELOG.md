CHANGELOG
=========

Version 1.0.2 work in progress
------------------------------

...

Version 1.0.1 on 25. Okt. 2014
------------------------------

- Fixed the `bin/markdown` script to work with composer autoloader (c497bada0e15f61873ba6b2e29f4bb8b3ef2a489)
- #74 fixed a bug that caused a bunch of broken characters when non-ASCII input was given. Parser now handles UTF-8 input correctly. Other encodings are currently untested, UTF-8 is recommended.

Version 1.0.0 on 12. Okt. 2014
------------------------------

This is the first stable release of version 1.0 which is incompatible to the 0.9.x branch regarding the internal API which is used when extending the Markdown parser. The external API has no breaking changes. The rendered Markdown however has changed in some edge cases and some rendering issues have been fixed.

The parser got a bit slower compared to earlier versions but is able to parse Markdown more accurately and uses an abstract syntax tree as the internal representation of the parsed text which allows extensions to work with the parsed Markdown in many ways including rendering as other formats than HTML.

For more details about the changes see the [release message of 1.0.0-rc](https://github.com/cebe/markdown/releases/tag/1.0.0-rc).

You can try it out on the website: <http://markdown.cebe.cc/try>

The parser is now also regsitered on the [Babelmark 2 page](http://johnmacfarlane.net/babelmark2/?normalize=1&text=Hello+**World**!) by [John MacFarlane](http://johnmacfarlane.net/) which you can use to compare Markdown output of different parsers.

Version 1.0.0-rc on 10. Okt. 2014
---------------------------------

- #21 speed up inline parsing using [strpbrk](http://www.php.net/manual/de/function.strpbrk.php) about 20% speedup compared to parsing before.
- #24 CLI script now sends all error output to stderr instead of stdout
- #25 Added partial support for the Markdown Extra flavor
- #10 GithubMarkdown is now fully supported including tables
- #67 All Markdown classes are now composed out of php traits
- #67 The way to extend markdown has changed due to the introduction of an abstract syntax tree. See https://github.com/cebe/markdown/commit/dd2d0faa71b630e982d6651476872469b927db6d for how it changes or read the new README.
- Introduced an abstract syntax tree as an intermediate representation between parsing steps.
  This not only fixes some issues with nested block elements but also allows manipulation of the markdown
  before rendering.
- This version also fixes serveral rendering issues.

Version 0.9.2 on 18. Feb. 2014 
------------------------------

- #27 Fixed some rendering problems with block elements not separated by newlines

Version 0.9.1 on 18. Feb. 2014
------------------------------

Fixed an issue with inline markers that begin with the same character e.g. `[` and `[[`.

Version 0.9.0 on 18. Feb. 2014
------------------------------

The initial release.

- Complete implementation of the original Markdown spec
- GFM without tables
- a command line tool for markdown parsing
