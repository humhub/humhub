# `clipboard-polyfill`

Make copying on the web as easy as:

    clipboard.writeText("This text is plain.");

As of October 2017, this library is a polyfill for the modern `Promise`-based [asynchronous clipboard API](https://www.w3.org/TR/clipboard-apis/#async-clipboard-api).

# Usage

Get the source using one of the following:

- Download [`build/clipboard-polyfill.js`](https://raw.githubusercontent.com/lgarron/clipboard-polyfill/master/build/clipboard-polyfill.js) and include it using a `<script>` tag.
- `npm install clipboard-polyfill`

## Write / Copy

Copy text:

    clipboard.writeText("hello world");

Copy other data types:

    var dt = new clipboard.DT();
    dt.setData("text/plain", "Fallback markup text.");
    dt.setData("text/html", "<i>Markup</i> <b>text</b>.");
    clipboard.write(dt);

Since copying only works in a user gesture, you should attempt it from inside an event listener, e.g. a button click listener.

## Read / Paste

Read text:

    // The success callback receives a string.
    // Fails if the clipboard does not contain `text/plain` data.
    clipboard.readText().then(console.log, console.error);

Read all data types:

    // The success callback receives a clipboard.DT object.
    clipboard.read().then(console.log, console.error);

Note that reading currently only works in Internet Explorer.

## Interface

    clipboard {
      static write:     (data: clipboard.DT)  => Promise<void>
      static writeText: (s: string) => Promise<void>
      static read:      () => Promise<clipboard.DT>
      static readText:  () => Promise<string>
      static suppressWarnings: () => void
    }

    clipboard.DT {
      constructor()
      setData: (type: string, value: string): void
      getData: (type: string): string | undefined
    }

## A note on `clipboard.DT`

The asynchronous clipboard API works like this:

    var dt = new DataTransfer();
    dt.setData("text/plain", "plain text");
    navigator.clipboard.write(dt);

Ideally, `clipboard-polyfill` would take a `DataTransfer`, so that the code above works verbatim when you replace `navigator.clipboard` with `clipboard`. However, *the `DataTransfer` constructor cannot be called* in most browsers. Thus, this library uses a light-weight alternative to `DataTransfer`, exposed as `clipboard.DT`:

    var dt = new clipboard.DT();
    dt.setData("text/plain", "plain text");
    clipboard.write(dt);


## This is way too complicated!

Try [this gist](https://gist.github.com/lgarron/d1dee380f4ed9d825ca7) for a simpler solution.


## [Can I use](http://caniuse.com/#feat=clipboard) it?

- Chrome 42+
- Firefox 41+
- Opera 29+
- Internet Explorer 9+ (text only)
- Edge
- Desktop Safari 10+
- iOS Safari 10+ (text only)

`clipboard-polyfill` uses a variety of heuristics to get around compatibility bugs. Please [let us know](https://github.com/lgarron/clipboard-polyfill/issues/new) if you are running into compatibility issues with any of the browsers listed above.

### Limitations

- In Microsoft Edge, it seems to be impossible to detect whether the copy action actually succeeded ([Edge Bug #14110451](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14110451/), [Edge Bug #14080262](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080262/)). `clipboard-polyfill` will always call `resolve()` in Edge.
- In Microsoft Edge, only the *first* data type you specify is copied to the clipboard ([Edge Bug #14080506](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080506/)).
  - `DataTransfer` and `clipbard.DT` keep track of the order in which you set items. If you care which data type Edge copies, call `setData()` with that data type first.
- On iOS Safari ([WebKit Bug #177715](https://bugs.webkit.org/show_bug.cgi?id=177715)) and Internet Explorer, only text copying works.
  - In other browsers, writing copy data that does *not* include the `text/plain` data type will succeed, but also show a console warning:

> clipboard.write() was called without a `text/plain` data type. On some platforms, this may result in an empty clipboard. Call clipboard.suppressWarnings() to suppress this warning.

- `clipboard-polyfill` attemps to avoid changing the document selection or modifying the DOM. However, `clipboard-polyfill` will automatically fall back to using them if needed:
  - On iOS Safari, the user's current selection will be cleared. This *should* not happen on other platforms unless there are unanticipated bugs. (Please [file an issue](https://github.com/lgarron/clipboard-polyfill/issues/new) if you observe this!)
  - On iOS Safari and under certain conditions on desktop Safari ([WebKit Bug #177715](https://bugs.webkit.org/show_bug.cgi?id=156529)), `clipbard-polyfill` needs to add a temporary element to the DOM. This will trigger a [mutation observer](https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver) if you have attached one to `document.body`. Please [file an issue](https://github.com/lgarron/clipboard-polyfill/issues/new) if you'd like to discuss how to detect temporary elements added by `clipboard-polyfill`.
- `read()` currently only works in Internet Explorer.
  - Internet Explorer can only read `text/plain` values from the clipboard.
- Internet Explorer does not have a native `Promise` implementation, so the standalone build file for `clipboard-polyfill` also includes `stefanpenner`'s [`es6-promise` polyfill](https://github.com/stefanpenner/es6-promise). This adds significant size to the build. Please [file an issue](https://github.com/lgarron/clipboard-polyfill/issues/new) if you're interested in a minimal build without Internet Explorer support.
