# Stream (JS)

The JavaScript stream API mirrors the PHP-side [stream concept](concept-stream.md) on the client. Use it when you need to drive a stream from JavaScript — custom filters, programmatic reloads, embedding a stream in your own widget.

The API lives under `humhub.modules.stream` and splits into these components:

| Class                       | Role                                                                            |
|-----------------------------|---------------------------------------------------------------------------------|
| `Stream`                    | Abstract base `ui.Widget`. Stream lifecycle and access to the other components. |
| `StreamState`               | Mutable state — `lastContentId`, `lastEntryLoaded`, `loading`.                  |
| `StreamEntry`               | Base class for entry-level operations (edit, delete, …).                        |
| `StreamRequest`             | Sends and tracks the actual XHR for entry batches.                              |
| `wall.WallStream`           | Concrete `Stream` used for space / profile / dashboard streams.                 |
| `wall.WallStreamFilter`     | Extensible filter for `WallStream`.                                             |

## Stream initialisation

When a stream is constructed, the following happens in order. Subclasses override the abstract steps (`initEvents`, `initScroll`, `onClear`).

1. `new Stream(container, options)` — subclasses should call `Stream.call(this, container, options)` in their own constructor.
2. `Stream.init()` runs once per stream:
   1. Sets up a fresh `StreamState` on `Stream.state`.
   2. `Stream.initWidget()`:
      1. `Stream.initEvents()` — abstract; bind widget-level listeners (e.g. `humhub:stream:beforeLoadEntries`).
      2. `Stream.initFilter()` — installs the filter from `options.filter` if present.
      3. `Stream.initScroll()` — abstract; wire up infinite scroll if applicable.
   3. `Stream.clear()` — hides the stream, removes existing entries and the loader, fires `humhub:stream:clear`, then calls the abstract `Stream.onClear()`.
   4. `Stream.loadInit()` — fires the first `StreamRequest`.
   5. `Stream.handleResponse()` — dispatches based on response shape:
      - If the response indicates the last entry was reached → `Stream.handleLastEntryLoaded()`.
      - If `options.insertAfter` is set → `Stream.handleInsertAfterResponse()` (entries are inserted after the named entry).
      - Otherwise → `Stream.handleLoadMoreResponse()` (default append).

## Reloading a stream entry

To re-render a single entry — typically after an edit or like that the server-side wall entry widget already knows how to display:

```js
var stream = humhub.modules.stream.getStream();
stream.reloadEntry(entryNode);
```

`entryNode` may be a DOM node, a `StreamEntry` instance, or an entry's content ID. The request hits the original stream URL with the entry's content ID and replaces the rendered HTML in place.

## Stream events

The stream widget fires events at the major lifecycle points:

| Event                                  | When                                       |
|----------------------------------------|--------------------------------------------|
| `humhub:stream:beforeLoadEntries`      | A `StreamRequest` is about to fire.        |
| `humhub:stream:afterAddEntries`        | New entries have been inserted in the DOM. |
| `humhub:stream:clear`                  | The stream is being reset (e.g. filter changed). |
| `humhub:stream:lastEntryLoaded`        | The end of the stream has been reached.    |

Subscribe via `Stream.on(...)` inside `initEvents()`, or globally:

```js
humhub.modules.event.on('humhub:stream:afterAddEntries', function (evt, stream, response) {
    // ...
});
```
