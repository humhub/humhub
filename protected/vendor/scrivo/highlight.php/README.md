highlight.php
=============

*highlight.php* is a server side code highlighter written in PHP that currently
supports over 135 languages. It's a port of [highlight.js]
(http://www.highlightjs.org) by Ivan Sagalaev that makes full use of the 
language and style definitions of the original JavaScript project.

Get started
===========

Make sure that the classes defined in _scrivo/Highlight_ can be found either
by inclusion or by an autoloader. A trivial autoloader for this purpose is 
included in this project. Alternatively you can use some other autoloading 
functionality, for example the one provided by composer.

Second, the [styles](#2-styles) directory must be located somewhere in your 
document root. The page that contains your highlighted code will need to
reference these CSS files.

The _Highlight\Highlighter_ class contains the highlighting functionality.
You can choose between two highlighting modes: explicit mode or automatic 
language detection mode.

Highlighting in explicit mode is rather straightforward, but please note the
CSS class set on the PRE element:

```php
<?php
// Initialize your autoloader (this example is using composer)
require 'vendor/autoload.php';
// Instantiate the Highlighter. 
$hl = new Highlight\Highlighter();
// Highlight some code. 
$r = $hl->highlight("ruby", file_get_contents("a_ruby_script.rb"));
// Output the code using the default stylesheet:
?>
<html>
    <head>
        <!-- Link to the stylesheets: -->
        <link rel="stylesheet" type="text/css" href="styles/default.css">
    </head>
    <body>
        <!-- Print the highlighted code: -->
        <pre class="hljs <?=$r->language?>"><?=$r->value?></pre>
    </body>
</html>
```

Alternatively you can use the autodetection mode. The thing to note
here is that you'll need to supply a list of languages that you want to detect
automatically. When using language autodetection the code to highlight is 
sampled using all given language definitions. This is rather inefficient when
you use the whole set of 70+ languages so a method is provided for you to 
limit this set to just those languages that are relevant for your project. 

```php
<?php
// Initialize your autoloader (this example is using composer)
require 'vendor/autoload.php';
// Instantiate the Highlighter. 
$hl = new Highlight\Highlighter();
// Set the languages you want to detect automatically. 
$hl->setAutodetectLanguages(array("ruby", "python", "perl"));
// Highlight some code. 
$r = $hl->highlightAuto(file_get_contents("a_ruby_script.rb"));
// Output the code using the default stylesheet:
?>
<html>
    <head>
        <!-- Link to the stylesheets: -->
        <link rel="stylesheet" type="text/css" href="styles/default.css">
    </head>
    <body>
        <!-- Print the highlighted code: -->
        <pre class="hljs <?=$r->language?>"><?=$r->value?></pre>
    </body>
</html>
```

Installation using Composer
===========================

Run _composer require_ from your command line:
```bash
composer require scrivo/highlight.php:8.*
```

Or add this to your _composer.json_ and run _composer install_:
```json
{
    "require": {
        "scrivo/highlight.php": "8.*"
    }
}
```

Add the from Composer prepared autoload file to your php file:
```php
require 'vendor/autoload.php';
```

Project structure
=================

The project contains the following folders:

1. [Highlight](#1-highlight)
2. [styles](#2-styles)
3. [demo](#3-demo)
4. [test](#4-test)
5. [tools](#5-tools)

##1. Highlight

This folder contains the main source and the following files (classes):

### Highlight.php (Highlight)

This is the one that does the highlighting for you and the one you'll probably
look for.

### Language.php (Language)

Auxiliary class used in the Highlight class. Instances of these classes 
represent the rather complex structures of regular expressions needed to 
scan through programming code in order to highlight them. 

You don't need this class.

### JsonRef.php (JsonRef)

Auxiliary class to decode JSON files that contain path based references. The 
language definition data from [highlight.js](http://www.highlightjs.org) is 
to complex to describe in ordinary JSON files. Therefore it was chosen to 
use [dojox.json.ref]
(https://dojotoolkit.org/reference-guide/1.9/dojox/json/ref.html) to export 
them. This class is able (to a very limited extend) to decode JSON data that
was created with this [dojo](https://dojotoolkit.org) toolkit.

This class has a very distinct purpose and might be useful in other projects
as well (and maybe a start of a new project ;) ).

### Autoloader.php (Autoloader)

A simple autoloader class that you possible might want or more likely not want
to use. It is used for the tools and tests.

### the languages folder

This folder contains all language definitions: one JSON file for each language.
These files are not hand coded but extracted from the original 
[highlight.js](http://www.highlightjs.org) project. 

##2. styles

These are the the CSS files needed to actually color the code. Not much to 
say about: these are just copied from the [highlight.js]
(https://github.com/isagalaev/highlight.js/tree/master/src/styles)
project.

##3. demo

This folder contains two demo pages that can be accessed through your browser.

### demo.php

A test page showing all supported languages and styles. 

### compare.php

Much like [demo.php](#demo-php) but this page focuses on the comparison 
between _highlight.php_ and _highlight.js_. Both should yield the same results.

##4. test

This folder contains the unit test for _highlight.php_. To run them run 
_phpunit_ from this directory:

```bash
	phpnunit .
```
Note that the following tests for _highlight.js_ are not rewritten for 
_highlight.php_:

### special explicitLanguage

Controlling language selection by setting an attribute to the containing DIV
is irrelevant for _highlight.php_

### special customMarkup

In highlight.js code may contain additional HTML markup like in the following
PHP fragment: '$sum = <b>$a</b> + $b;'. Currently this is not supported by
_highlight.php_. _highlight.php_ can only highlight (unescaped) code. Also 
HTML breaks <br> code to highlight is not supported. _highlight.php_ does 
support tab replacement (defaults to 4 spaces).

### special noHighlight

There is no need to turn off highlighting through a class name on the code
container.

### special buildClassName

_highlight.php_ does not modify class names of code containers.

##5. tools

A collection of scripts that are used to extract data from the original 
[highlight.js](http://www.highlightjs.org) project.

Note that these scripts require the installation of the following software 
on your box: _highlight.js_, dojo and node.js.

To export the language definitions from _highlight.js_ continue as follows:

Download a source distribution of 
[_highlight.js_](https://github.com/isagalaev/highlight.js/releases), and 
create a node build:

```bash
	nodejs tools/build.js -t node
```	

Download a source distribution of [dojo](http://download.dojotoolkit.org/).

Make sure that the path references in launcer.js are correct and run it:

```bash
	nodejs launcher.js > languages.dat
```	
	
If all went right you'll now have a file with all language definitions. Run
get_language_defintions.php to generate the json files for the individual
lauagues:

```bash
	php get_language_definitions.php
```	

Some History
============

Geert Bergman  
Sep 30, 2013

JavaScript code highlighting is very convenient and in many cases just what
you want to use. Especially for programming blogs I would not advice you to
use otherwise. But there are occasions where you're better off with a more 
'static' approach, for instance if you want to send highlighted code in an 
email or for API documents. For this I needed a code highlighting program 
preferably written in PHP.

I couldn't found any satisfactory PHP solution so I decided to port one from
JavaScript. After some comparison of different highlighting programs 
based on license, technology, language support 
[highlight.js](http://www.highlightjs.org) came out most
favorable in my opinion.

It was my decision not to make a PHP highlighter but to do a port of 
highlight.js, these are different things. The goal was to make it work
exactly as [highlight.js](http://www.highlightjs.org) to make as much use 
as possible of the language definitions and CSS files of the original program. 

Happy coding!
