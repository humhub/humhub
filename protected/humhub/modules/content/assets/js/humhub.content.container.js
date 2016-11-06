/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('content.container', function (module, require, $) {
    var client = require('client');
    
    var follow = function(evt) {
        var containerId = evt.$trigger.data('content-container-id');
        client.post(evt).then(function(response) {
            evt.$trigger.hide();
            $('[data-content-container-id="'+containerId+'"].unfollowButton').addClass('animated bounceIn').show();
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };
    
    var unfollow = function(evt) {
        var containerId = evt.$trigger.data('content-container-id');
        client.post(evt).then(function(response) {
            evt.$trigger.hide();
            $('[data-content-container-id="'+containerId+'"].followButton').addClass('animated bounceIn').show();
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };
    
    module.export({
        follow: follow,
        unfollow: unfollow
    });
});