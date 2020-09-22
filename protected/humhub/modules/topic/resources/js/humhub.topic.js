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
    var client = require('client');
    var loader = require('ui.loader');
    var Widget = require('ui.widget').Widget;

    var addTopic = function (evt) {
        var streamFilter = Widget.instance($('#wall-stream-filter-nav'));

        if(!streamFilter || !streamFilter.getFilterById('topic') && evt.$trigger.data('topic-url')) {
            client.pjax.redirect(evt.$trigger.data('topic-url'));
            return;
        }

        var topicId = evt.$trigger.data('topic-id');

        if (topics[topicId]) {
            return;
        }

        topics[topicId] = getTopicFromTrigger(evt.$trigger);

        updated();
    };

    var getTopicFromTrigger = function ($trigger) {
        var id = $trigger.data('topic-id');
        var name = $trigger.find('.label').text();
        var $linked = getRemoveLabel({id:id, name:name});
        return {
            id: $trigger.data('topic-id'),
            name: name,
            $label: $linked,
            icon: module.config.icon
        };
    };

    var getRemoveLabel = function(topic) {
        return $(string.template(module.template.removeLabel, {id: topic.id, name: string.encode(topic.name), icon: module.config.icon}));
    };

    var removeTopic = function (evt) {
        var topic = getTopicFromTrigger(evt.$trigger);
        delete topics[topic.id];
        updated();
    };

    var getTopics = function () {
        return $.extend({}, topics);
    };

    var setTopics = function(newTopics) {
        topics = {};
        newTopics.forEach(function(topic) {
            topic.$label = getRemoveLabel(topic);
            topic.icon = module.config.icon;
            topics[topic.id] = topic;
        });

        updated();
    };

    var updated = function() {
        event.trigger('humhub:topic:updated', [getTopicArray()]);
    }

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

    var removeOverviewTopic = function(evt) {
        var $row = evt.$trigger.closest('[data-key]');
        var $nameTd = $row.find('td:first');
        var name = $nameTd.text();
        var $loader = loader.set($('<span>').text(name),  {size: '10px', css: {padding: '0px'}});
        $nameTd.html($loader);
        client.post(evt).then(function(response) {
            if(response.success) {
                $row.remove();
                module.log.success(response.message, true);
            }
        }).catch(function(e) {
            module.log.error(e, true);
            loader.reset($loader);
        });
    };

    var unload = function() {
        //Todo: remember active topics by space?
        topics = {};
    };

    module.template = {
        'removeLabel': '<a href="#" class="topic-remove-label" data-action-click="topic.removeTopic" data-topic-id="{id}"><span class="label label-default animated bounceIn">{icon} {name}</span></a>'
    };


    module.export({
        addTopic: addTopic,
        setTopics: setTopics,
        removeTopic: removeTopic,
        removeOverviewTopic: removeOverviewTopic,
        getTopics: getTopics,
        getTopicIds: getTopicIds,
        unload: unload
    });
});