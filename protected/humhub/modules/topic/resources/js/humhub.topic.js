/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

humhub.module('topic', function (module, require, $) {

    var event = require('event');
    var topics = {};
    var string = require('util').string;

    var addTopic = function (evt) {
        var topicId = evt.$trigger.data('topic-id');

        if (topics[topicId]) {
            return;
        }

        topics[topicId] = getTopicFromTrigger(evt.$trigger);
        event.trigger('humhub:topic:added', topics[topicId]);
    };

    var getTopicFromTrigger = function ($trigger) {
        var id = $trigger.data('topic-id');
        var name = $trigger.find('.label').text();
        var $linked = getRemoveLabel({id:id, name:name});
        return {
            id: $trigger.data('topic-id'),
            name: $trigger.find('.label').text(),
            $label: $linked
        };
    };

    var getRemoveLabel = function(topic) {
        return $(string.template(module.template.removeLabel, {id: topic.id, name: topic.name}))
    };

    var removeTopic = function (evt) {
        var topic = getTopicFromTrigger(evt.$trigger);
        delete topics[topic.id];
        event.trigger('humhub:topic:removed', topic);
    };

    var getTopics = function () {
        return $.extend({}, topics);
    };

    var setTopics = function(newTopics) {
        topics = {};
        newTopics.forEach(function(topic) {
            topic.$label = getRemoveLabel(topic);
            topics[topic.id] = topic;
        });
        event.trigger('humhub:topic:updated', [getTopicArray()]);
    };

    var getTopicIds = function () {
        return Object.keys(topics) || [];
    };

    var getTopicArray = function() {
        var result = [];
        $.each(topics, function(id, topic) {
            result.push(topic);
        });
        return result;
    };

    var unload = function() {
        //Todo: remember active topics by space?
        topics = {};
    };

    module.template = {
        'removeLabel': '<a href="#" class="topic-remove-label" data-action-click="topic.removeTopic" data-topic-id="{id}"><span class="label label-default animated bounceIn"><i class="fa fa-star"></i> {name}</span></a>'
    };


    module.export({
        addTopic: addTopic,
        setTopics: setTopics,
        removeTopic: removeTopic,
        getTopics: getTopics,
        getTopicIds: getTopicIds,
        unload: unload
    });
});