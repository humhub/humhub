# Copying


# Test results

Platforms tested:
- Chrome 61.0.3163.100 (macOS 10.13.0)
- Safari 11.0 (macOS 10.13)
- Safari 11.0 (iOS 11.0 on an iPhone SE)
- Edge 15.15063 (Windows 10.0 in a VirtualBox VM)
- Firefox 54.0 (macOS 10.13)

|   | Chrome 61 | Safari 11 (macOS) | Safari 11 (iOS) | Edge 15 | Firefox 54 |
|---|---|---|---|---|---|
|`supported` always returns true †|✅|✅|✅|✅|✅|
|`enabled` **without** selection returns true †|❌|❌|❌|❌|✅|
|`exec` works **without** selection †|✅|⚠️¹|⚠️¹|✅|✅|
|`enabled` **with** selection returns true †|✅|✅|✅|✅|✅|
|`exec` works **with** selection †|✅|✅|✅|✅|✅|
|`exec` fails outside user gesture |✅|✅|✅|✅|✅|
|`setData()` in listener works|✅|✅|❌ ²|✅|✅|
|`getData()` in listener shows if `setData()` worked|✅|✅|⚠️ ²|❌ ³|✅|
|Copies all types set with `setData()`|✅|✅|✅|❌ ⁴|✅|
|`exec` reports success correctly|✅|✅|⚠️ ²|❌ ⁵|✅|
|`contenteditable` does not break document seleciton|❌|❌|❌|✅|✅|
|Can construct `new DataTransfer()`|✅|❌|❌|❌|❌|

† Here, we are only specifically interested in the case where the handler is called directly in response to a user gesture. I didn't test for behaviour when there is no user gesture.

- ¹ `document.execCommand("copy")` triggers a successul copy action, but listeners for the document's `copy` event aren't fired. [WebKit Bug #177715](https://bugs.webkit.org/show_bug.cgi?id=156529)
- ² [WebKit Bug #177715](https://bugs.webkit.org/show_bug.cgi?id=177715)
- ³ [Edge Bug #14110451](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14110451/)
- ⁴ [Edge Bug #14080506](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080506/)
- ⁵ [Edge Bug #14080262](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080262/)

## `supported` always returns true

In all browsers, `document.queryCommandSupported("copy")` always returns true.

## `enabled` **without** selection returns true (see issue 1 below)

When nothing on the page is selected, `document.queryCommandEnabled("copy")` returns true in Firefox, but not any other browsers.

## `exec` fires listener **without**  selection

On all platforms, `document.execCommand("copy")` always works (triggers a copy) during a user gesture, regardless of whether anything on the page is selected. However, on Safari listeners registered using `document.addEventListener("copy")` don't fire (and therefore don't have an opportunity to set the data on the clipboard).

## `enabled` **with** selection returns true

On all browsers, `document.queryCommandEnabled("copy")` returns true during a user gesture if some part of the page is selected (doesn't matter which part; can be the entire body on a single element). The selection may be made using Javascript during the user gesture handler itself.

## `exec` fires listener **with** selection

On all platforms, `document.execCommand("copy")` works during a user gesture, regardless of whether anything on the page is selected. Listeners registered with ``document.addEventListener("copy")` fire.

## `enabled` returns false outside user gesture

In all browsers, `document.execCommand("copy")` fails when there is no user gesture, and returns `false`.

## `setData()` works in listener (see issues 3/4 below)

This means that the following works:

    document.addEventListener("copy", function(e) {
      e.clipboardData.setData("text/plain", "plain text")
      e.preventDefault();
    });

On iOS, the `setData` call doesn't work – it actually empties the clipboard (at least for that data type). This is supposedly fixed in WebKit as of September 19, 2017: <https://bugs.webkit.org/show_bug.cgi?id=177715>
Fortunately, it is possible to detect Safari's behaviour (when the value is not empty), because the following returns `""` even after the `setData()` call:

      e.clipboardData.getData("text/plain", "plain text")

## `getData()` in listener shows if `setData()` worked

In Edge, `setData()` works inside the copy listener, but `getData` never reports the data that was set, and returns the empty string instead.

Note that on iOS Safari, `getData()` also returns the empty string, but since `setData()` doesn't work, this is the correct return value (And can be used to detect if setting the string succeeded).

## Copies all types set with `setData()` (see issue 2 below)

This means that the following listeners put both plain text and HTML on the clipboard:

    document.addEventListener("copy", function(e) {
      e.clipboardData.setData("text/plain", "plain text")
      e.clipboardData.setData("text/html", "<b>markup</b> text")
      e.preventDefault();
    });

    document.addEventListener("copy", function(e) {
      e.clipboardData.setData("text/html", "<b>markup</b> text")
      e.clipboardData.setData("text/plain", "plain text")
      e.preventDefault();
    });

Edge only places the *last* provided data type on the clipboard.

## `exec` reports success correctly (see issue 5 below)

Most platforms correctly report if `document.execCommand("copy")` successfully copied something to the clipboard.

On iOS, `document.execCommand("copy")` also returns `true` when `event.clipboardData.setData()` clears the clipboard. In this case, the clipboard is set to empty, but the return value is arguably correct once we account for the relevant bug.

Edge, however, *always* returns `false`. Even when the copy attempt succeeds.

## `contenteditable` does not break document seleciton

Consider the following code:

    var sel = document.getSelection();
    var range = document.createRange();
    range.selectNodeContents(document.body);
    sel.addRange(range);

This fails in Chrome and Safari if the last content in the DOM is the following:

    <div contenteditable="true" class="editable"></div>

## Can construct `new DataTransfer()` (see issue 6 below)

The new asynchronous clipboard API takes a `DataTranfer` input. However, you can only call the DataTransfer constructor in Chrome right now. (The constructor was made publicly callable specifically for the async clipboard API.)


# Strategy

Firstly

- **Issue 1**: `queryCommandEnabled()` doesn't tell us when copying will work.
  - Workaround: Don't consult `queryCommandEnabled()`; just try `execCommand()` every time.

All platforms except iOS can share the same default implementation. However:

- **Issue 2**: Edge will only put the first provided data type on the clipboard.
  - Workaround: File a bug against Edge. (Started: [Edge Bug #14080506](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080506/))
  - Document that the caller should add the most important data type to the copy data first.

TODO: Add "Safari doesn't trigger listener without selection" issue.

iOS Safari requires the trickiest fallback:

- **Issue 3**: For iOS Safari, it seems we can't attach data types in the listener at all.
  - Workaround: Detect the issue, and fall back to copying the `text/plain` data type with a different mechanism.
  - Document that callers should always provide a `text/plain` data type if they want copying to work on iOS.

The logic will be as follows:
- Is there a `text/plain` data type in the input:
  - No? ⇒ No fallback. Clipboard will likely end up blank on iOS. (Consider warning the user if they don't provide a value for the `text/plain` data type.)
  - Yes? ⇒ Check `setData()` against `getData()` for the `text/plain` data type. Do they match?
    - Yes? ⇒ Do nothing. (This will result in a blank clipboard when the copied string is empty.)
    - No? ⇒ Fall back.

We fall back creating a temporary DOM element, assigning the `text/plain` value to it using `textContent`, selecting it using Javascript, and triggering `execCommand("copy")` again. (The repeated copy command appears to work on iOS.) We will place the element within a shadow root in order to prevent outside formatting (e.g. page background color) from affecting the text, and use `white-space: pre-wrap` to preserve newlines and whitespace. However:

- **Issue 4**: On iOS, the copied text will still have the explicit formatting style of the default text in shadow root (issue 3)
  - Workaround: none.
  - Document this.

The Windows problem looks a bit annoying.

On Windows, we perform the copy, but we will always get back `false`. 

- **Issue 5**: On Windows, `execCommand("copy")` always returns false.
  - Workaround 0: Report this bug to Edge, and hope they fix it. (Started: [Edge Bug #14080262](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/14080262/))
  - Workaround 1: Pass on the return value blindly, and document that Windows has a bug.
  - Workaround 2: Never check the return value of `execCommand("copy")`
  - Workaround 3: Detect Edge using a different mechanism (e.g. UA sniffing), and ignore the return value only when we think we're in Edge.

We also need to add some more polyfilling than we might like:

- **Issue 6**: The caller can't construct a `DataTransfer` to pass to the polyfill on any platform except Chrome..
  - Workaround: Provide an object with a sufficiently ergonomic subset of the interface of `DataTransfer` that the caller can use. (We can swap out the implementation with `DataTransfer` as platforms allow calling the constructor directly.)

- **Issue 7**: Internet Explorer did its own thing.
  - Workaround: [old implementation](https://github.com/lgarron/clipboard-polyfill/blob/94c9df4aa2ce1ca1b08280bf36923b65648d9f72/clipboard-polyfill#L167) using `window.clipboardData`. Requires a `Promise` polyfill. :-/
