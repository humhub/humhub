Stream
=======

This section describes the new (since v1.3) Javascript Stream API.

Please refer to the main [Stream Section](stream.md) for more information about Streams.

Since HumHub v1.3 the Javascript Stream API was split into the following main components within the `humhub.modules.stream` namespace:

- `Stream` serves as abstract `ui.Widget` class for all streams and provides the basic stream logic as initialization of a stream and accessing other components of the stream.
- `StreamState` contains the current state of the stream as for example `lastContentId`, `lastEntryLoaded`, `loading`
- `StreamEntry` serves as base class for all stream entries and implements basic logic for accessing the underlying content (edit/delete/etc.) of a single stream entry.
- `StreamRequest` is responsible for requesting and reloading stream entries
- `wall.WallStream` extends the abstract `Stream` and is used for example for the space, profile and dashboard streams
- `wall.WallStreamFilter` is used as extensible filter component for the wall stream

######  Stream Initialization

The initialization of a stream is handled as follows:

1. Constructor call (subclasses should call `Stream.call(this, container, options);` in case of an overwritten constructor);
2. Call of widgets `Stream.init()`
    1. Initialize a new `StreamState` instance as `Stream.state` 
    2. Call of `Stream.initWidget` which is only called once for a stream
        1. Call of abstract function `Stream.initEvents()` which can be overwritten to define widget event listeners as `humhub:stream:beforeLoadEntries`
        2. Call of `Stream.initFilter()` sets an optional stream filter defined in `options.filter`
        3. Call of abstract function `Stream.initScroll()` can be used to define auto scroll loading
    3. Call of `Stream.clear()` to reset the stream
        1. Hides the stream
        2. Removes all stream entries
        3. Removes the stream loader
        4. Triggers `humhub:stream:clear`
        5. Call of abstract function `Stream.onClear()` which can be used for further cleanups
    4. Call of `Stream.loadInit()` which requests the initial entries of the stream by means of a `StreamRequest` call
    5. Call of `Stream.handleResponse` in case there was no error
        1. In case the response contains the last entry call `Stream.handleLastEntryLoaded()`
        2. In case the request options `insertAfter` is set call `Stream.handleInsertAfterResponse()` which appends the resulted entries after a certain StreamEntry
        3. Else call the default `Stream.handleLoadMoreResponse()`
        

######  StreamEntry loading:

 
######  Reload a stream entry:
