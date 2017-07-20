# `clipboard.js`

Make copying on the web as easy as:

    clipboard.copy("This text is plain.");

Note: in most browsers, copying is only allowed if `clipboard.copy()` is triggered in direct response to a user gesture like a click or a key press.


### Copy rich text

    clipboard.copy({
      "text/plain": "Markup text. Paste me into a rich text editor.",
      "text/html": "<i>Markup</i> <b>text</b>. Paste me into a rich text editor."
    });


### Copy a DOM node as markup

    clipboard.copy(document.body);

(Uses [XMLSerializer](http://caniuse.com/#search=XMLSerializer).)


### Use the `copy` outcome as a Promise (optional):

    clipboard.copy("test").then(
      function(){console.log("success");},
      function(err){console.log("failure", err);}
    );


### Paste

Pasting plain strings currently works in IE.

    clipboard.paste().then(
      function(result) {console.log(result);},
      function(err) {console.log("failure", err);}
    );


## Usage

Get the source using one of the following:

- `clipboard.js` or `clipboard.min.js`
- `npm install clipboard-js`
- `bower install clipboard.js`

Load the script:

    <script src="clipboard.js"></script>

Then copy a `string` or an `object` (mapping [data types](http://www.w3.org/TR/clipboard-apis/#mandatory-data-types-1) to values) as above.


## What about [zenorocha/clipboard.js](https://github.com/zenorocha/clipboard.js)?

This project is half a year older. :-P  
I created it partially to test the clipboard API while reviewing it for Chrome (I work on Chrome security), and partially to use in [my own project](https://alg.cubing.net/).

I wouldn't have created this project if `zenorocha/clipboard.js` had already existed, but both projects have different uses right now. The fundamental difference is that this project hijacks the copy event, while `zenorocha/clipboard.js` uses fake element selection. Some details (as of November 2015):

This project                                       | `zenorocha/clipboard.js`
---------------------------------------------------|--------------------------
Supports plain strings, `text/html`, and DOM nodes | Only supports plain strings
≈100 lines                                         | ≈700 lines
1.5KB minimized + gzipped                          | 2.9KB minimized + gzipped
Doesn't change document selection                  | Clears document selection
Only an imperative API (`clipboard.copy()`)        | Declarative DOM-based API
Uses `Promise`s                                    | -
Supports paste (in IE)                             | -
-                                                  | Offers a fallback prompt (`Press Ctrl+C to copy`)


## This is way too complicated!

Try [this gist](https://gist.github.com/lgarron/d1dee380f4ed9d825ca7) for a simpler solution.


## [Can I use](http://caniuse.com/#feat=clipboard) it?

- Chrome 42+
- Firefox 41+
- Opera 29+
- Internet Explorer 9+
- Safari 10+
