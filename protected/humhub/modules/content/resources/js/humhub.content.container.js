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
            additions.switchButtons(evt.$trigger, $('[data-content-container-id="'+containerId+'"].unfollowButton'));
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };
    
    var unfollow = function(evt) {
        var containerId = evt.$trigger.data('content-container-id');
        client.post(evt).then(function(response) {
            additions.switchButtons(evt.$trigger, $('[data-content-container-id="'+containerId+'"].followButton'));
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };
    
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
    
    module.export({
        follow: follow,
        unfollow: unfollow,
        enableModule: enableModule,
        disableModule: disableModule
    });
});