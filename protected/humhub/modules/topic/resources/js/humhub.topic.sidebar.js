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
        const showMoreButton = evt.$trigger;
        const moreTopicsBlock = this.getMoreTopicsBlock();

        if (moreTopicsBlock.length) {
            moreTopicsBlock.show();
            this.getShowLessButton().show();
            showMoreButton.hide();
            evt.finish();
            return;
        }

        client.get(evt).then(function (response) {
            showMoreButton.before(response.topics)
                .after(response.button)
                .hide()
                .removeAttr('data-ui-loader');
            loader.remove(showMoreButton);
        }).catch(function (e) {
            module.log.error(e, true);
        }).finally(function () {
            evt.finish();
        });
    }

    Sidebar.prototype.showLess = function (evt) {
        evt.$trigger.hide();
        this.getMoreTopicsBlock().hide();
        this.getShowMoreButton().show();
    }

    Sidebar.prototype.getMoreTopicsBlock = function (evt) {
        return this.$.find('.topic-sidebar-more-topics');
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
