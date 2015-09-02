Console Normalizer
==================

IE, oh IE, how we fight... this utility fixes issues with IE&#39;s console object so you can use it just like a real console.

Usage
-----

```html
    <script src="normalizeconsole.min.js"></script>
    <script>
        console.group('Play around');
        console.time('timer');

        console.log('hello'); // does not blow up if IE console is closed

        console.log.apply(console, ['one', 'two', 'three']); // does not cause an Invocation error

        typeof(console.log); // function (some things you shouldn't have to say...)

        console.info();
        console.warn();
        console.error();

        console.timeEnd('timer');
        console.groupEnd();
    </script>
```

What it fixes
-------------

IE 8 & 9's console objects are a bit... well, it's IE, our favorite stepchild. To be fair, Chrome has a few similar issues which are fixed by this utility as well. Here's a quick summary, with details below:

 * window.console available in IE, even if it is closed
 * console.log/info/warn/error.apply work in IE and Chrome
 * console.time and console.timeEnd supported
 * console.group and console.groupEnd supported
 * Function.prototype.bind added if it doesn't exist (for IE 8)

### console is null or not an object

The `window.console` object is only availble in IE 8/9 when it is opened; this is also true in Chrome.

### console.group and console.groupEnd

Implements compatible functions for `group` and `groupEnd`

### console.time and console.timeEnd

Implements compatible functions for `time` and `timeEnd`

### Function.prototype.apply

Calling `apply` on any of the logging methods (i.e. `console.log.apply(console, arguments)`) breaks with "Object doesn't support this property or method" in IE8  and a Method Invocation Error in IE 9. This is [because it is a "host object" and does not extend Object](http://stackoverflow.com/questions/5538972/console-log-apply-not-working-in-ie9).

### Function.prototype.bind in IE 8

As a side effect, this utility also implements a compatible version of Function.prototype.bind for IE 8.

Compatibility
-------------

Compatible with everything after IE 5.5

Contributing
------------

Clone, modify, and submit pull requests via [GitHub](https://github.com/katowulf/console-normalizer).

To create the minimized file, use [Dean Edward's packer](http://dean.edwards.name/packer/).

License
-------
(The MIT License)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Support
-------

https://github.com/katowulf/console-normalizer