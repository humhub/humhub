/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.module('content.container', function (module, require, $) {
    var client = require('client');
    var additions = require('ui.additions');

    var follow = function(evt) {
        var containerId = evt.$trigger.data('content-container-id');
        client.post(evt).then(function(response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, $('[data-content-container-id="' + containerId + '"].unfollowButton'));
                if (response.space) {
                    require('space.chooser').SpaceChooser.instance($('#space-menu-dropdown')).appendItem(response.space);
                }
            }
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };

    var unfollow = function(evt) {
        var containerId = evt.$trigger.data('content-container-id');
        client.post(evt).then(function(response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, $('[data-content-container-id="' + containerId + '"].followButton'));
                if (response.space) {
                    require('space.chooser').SpaceChooser.instance($('#space-menu-dropdown')).removeItem(response.space);
                }
            }
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };

    var relationship = function(evt) {
        var postOptions = {};
        var buttonOptions = evt.$trigger.data('button-options');
        if (buttonOptions) {
            postOptions.data = {options: buttonOptions};
        }
        client.post(evt, postOptions).then(function(response) {
            var oldButton = evt.$trigger;

            // Replace previous button with new
            if (oldButton.closest('.btn-group').length) {
                oldButton = oldButton.closest('.btn-group');
            }
            oldButton.hide().after(response.data);
            var newButton = oldButton.next();
            oldButton.remove();

            // Show/Hide current buttons if they depend on status of new button
            if (newButton.data('show-buttons')) {
                newButton.parent().find(newButton.data('show-buttons')).show();
            }
            if (newButton.data('hide-buttons')) {
                newButton.parent().find(newButton.data('hide-buttons')).hide();
            }
        }).catch(function(e) {
            module.log.error(e, true);
        });
    }

    var enableModule = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.disable'));
                evt.$trigger.siblings('.moduleConfigure').fadeIn('fast');
            }
            if(evt.$trigger.data('reload')) {
                client.reload();
            }
        }).catch(function(err) {
            module.log.error(err, true);
        }).finally(function() {
            evt.finish();
        });
    };

    var disableModule = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.enable'));
                evt.$trigger.siblings('.moduleConfigure').fadeOut('fast');
            }
            if(evt.$trigger.data('reload')) {
                client.reload();
            }
        }).catch(function(err) {
            module.log.error(err, true);
        }).finally(function() {
            evt.finish();
        });
    };

    var guid = function () {
        return module.config.guid;
    };

    var unload = function () {
        module.config.guid = null;
    };

    module.export({
        follow: follow,
        unfollow: unfollow,
        relationship: relationship,
        unload: unload,
        guid: guid,
        enableModule: enableModule,
        disableModule: disableModule
    });
});
