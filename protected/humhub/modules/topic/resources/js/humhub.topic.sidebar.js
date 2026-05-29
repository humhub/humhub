/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

humhub.module('topic.sidebar', function (module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');
    const Widget = require('ui.widget').Widget;

    const Sidebar = Widget.extend();

    Sidebar.prototype.showMore = function (evt) {
        const that = this;
        const showMoreButton = evt.$trigger;

        if (this.getShowLessButton().length) {
            this.getMoreTopics().show();
            this.getShowLessButton().show();
            showMoreButton.hide();
            evt.finish();
            return;
        }

        client.get(evt).then(function (response) {
            that.getTopicsList().append(response.topics);
            showMoreButton.hide().after(response.button);
            loader.remove(showMoreButton.removeAttr('data-ui-loader'));
        }).catch(function (e) {
            module.log.error(e, true);
        }).finally(function () {
            evt.finish();
        });
    }

    Sidebar.prototype.showLess = function (evt) {
        evt.$trigger.hide();
        this.getMoreTopics().hide();
        this.getShowMoreButton().show();
    }

    Sidebar.prototype.getTopicsList = function (evt) {
        return this.$.find('.topic-label-list');
    }

    Sidebar.prototype.getMoreTopics = function (evt) {
        return this.getTopicsList().find('.link-topic-more');
    }

    Sidebar.prototype.getShowMoreButton = function (evt) {
        return this.$.find('[data-action-click=showMore]');
    }

    Sidebar.prototype.getShowLessButton = function (evt) {
        return this.$.find('[data-action-click=showLess]');
    }

    module.export({
        Sidebar,
    });
});
