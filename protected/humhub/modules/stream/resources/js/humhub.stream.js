/**do
 * Core module for managing Streams and StreamItems
 * @type Function
 */

humhub.module('stream', function (module, require, $) {

    var Component = require('action').Component;
    var event = require('event');

    /**
     * Returns a stream instance for the given selector. If no selector is given this function returns the main wall stream.
     *
     * @param $selector
     * @deprecated since v1.3 use Component.instance instead
     * @returns {*}
     */
    var getStream = function ($selector) {
        $selector = $selector || '#wallStream';
        return Component.instance($($selector));
    };

    /**
     * Returns a single entry with a given contentId of the wall stream
     *
     * @deprecated since v1.3 use Component.instance instead
     * @param contentId
     */
    var getEntry = function (contentId) {
        return module.getStream().entry(contentId);
    };

    var unload = function() {
        event.off('.stream');
    };

    module.export({
        unload: unload,
        getStream: getStream,
        getEntry: getEntry
    });
});
