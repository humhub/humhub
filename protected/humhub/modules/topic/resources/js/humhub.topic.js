/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

humhub.module('topic', function (module, require, $) {

    var event = require('event');
    var topics = {};

    var addTopic = function(evt) {
        var topicId = evt.$trigger.data('topic-id');

        if(topics[topicId]) {
            return;
        }

        topics[topicId] = {
            id: topicId,
            $label: evt.$trigger.find('.label').data('topic-id', topicId).clone()
        };

        event.trigger('humhub:topic:added', topics[topicId]);
    };


    module.export({
        addTopic: addTopic,
        getTopics: function() {
            return $.extend({}, topics);
        },
        getTopicArray: function() {
            return Object.keys(topics);
        }
    });
});