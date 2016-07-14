js-search
=========

This is a client side search engine for use on static pages.

It uses a pre-compiled search index to add a fulltext search to static HTML pages such as
[github pages][] or offline API documentation. The index is built by a PHP script using a
similar yet much more simplified and dump approach than the popular search engine [Lucene].

To see how it looks like, check out the [demo][].

[github pages]: https://pages.github.com/
[Lucene]: http://lucene.apache.org/
[demo]: http://cebe.github.io/js-search/#demo


Installation
------------

PHP 5.4 or higher is required to run the index generator.

Installation is recommended to be done via [composer][] by adding the following to the `require` section in your `composer.json`:

```json
"cebe/js-search": "*"
```

Run `composer update` afterwards.


Usage
-----

TODO.

See [example.html](example.html) for an implementation.

### Generate the index

Using the command line tool:
```
vendor/bin/jsindex <path-to-your-html-files>
```

This will generate a `jssearch.index.js` file that you have to include in the Html header.
