humhub.module('like', function (module, require, $) {
    var client = require('client');
    var additions = require('ui.additions');
    var Component = require('action').Component;

    var toggleLike = function (evt) {
        client.post(evt).then(function (response) {
            if(response.currentUserLiked) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.unlike'));
                var component = Component.closest(evt.$trigger);
                if(component) {
                    component.$.trigger('humhub:like:liked');
                }
            } else {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.like'));
            }
            
            _updateCounter(evt.$trigger.parent(), response.likeCounter);
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    var _updateCounter = function($element, count) {
        if (count) {
            $element.find(".likeCount").html('(' + count + ')').show();
        } else {
            $element.find(".likeCount").hide();
        }

    };

    module.export({
        toggleLike: toggleLike
    });
});