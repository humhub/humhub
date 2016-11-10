/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('content.container', function (module, require, $) {
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
    
    module.export({
        follow: follow,
        unfollow: unfollow
    });
});