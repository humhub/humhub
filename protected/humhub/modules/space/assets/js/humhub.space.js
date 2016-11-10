/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('space', function (module, require, $) {
    var client = require('client');
    var additions = require('ui.additions');
    var event = require('event');
    
    // Current space options (guid, image)
    var options;
    
    var init = function() {
        if(!module.isSpacePage()) {
            options = undefined;
        }
    };
    
    var isSpacePage = function() {
        return $('.space-layout-container').length > 0;
    };
    
    var setSpace = function(spaceOptions) {
        if(!options || options.guid !== spaceOptions.guid) {
            options = spaceOptions;
            event.trigger('humhub:modules:space:changed', $.extend({}, options));
        }
    };

    var enableModule = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.disable'));
            }
        }).catch(function(err) {
            module.log.error(err, true);
        });
    };

    var disableModule = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.enable'));
            }
        }).catch(function(err) {
            module.log.error(err, true);
        });
    };

    module.export({
        init: init,
        isSpacePage: isSpacePage,
        setSpace: setSpace,
        enableModule: enableModule,
        disableModule: disableModule
    });
});