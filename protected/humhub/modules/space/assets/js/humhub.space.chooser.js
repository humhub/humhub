/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('space.chooser', function (module, require, $) {
    var event = require('event');
    var space = require('space');

    module.initOnPjaxLoad = false;

    var init = false;

    var init = function () {
        event.on('humhub:ready', function () {
            if (!space.isSpacePage()) {
                module.setNoSpace();
            }
        }).on('humhub:modules:space:changed', function (evt, options) {
            module.setSpace(options);
        });
    };

    var setNoSpace = function () {
        module.getSpaceMenu().html(module.config.noSpace);
    };

    var setSpace = function (spaceOptions) {
        getSpaceMenu().html($(spaceOptions.image + '<b class="caret"></b>'));
    };

    var getSpaceMenu = function () {
        return  $('#space-menu');
    };


    module.export({
        init: init,
        setSpace: setSpace,
        setNoSpace: setNoSpace,
        getSpaceMenu: getSpaceMenu,
    });
});