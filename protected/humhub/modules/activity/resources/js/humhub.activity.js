/**
 * Core module for managing Activities
 */
humhub.module('activity', function (module, require, $) {
    const Widget = require('ui.widget').Widget;
    const client = require('client');
    const loader = require('ui.loader');

    const ActivityBox = Widget.extend();

    ActivityBox.prototype.init = function () {
        this.initLoadMore();

        this.$.niceScroll({
            cursorwidth: "7",
            cursorborder: "",
            cursorcolor: "#555",
            cursoropacitymax: "0.2",
            nativeparentscrolling: false,
            railpadding: {top: 0, right: 3, left: 0, bottom: 0}
        });
    }

    ActivityBox.prototype.getEndIndicator = function () {
        return this.$.find('.stream-end');
    }

    ActivityBox.prototype.isLoading = function () {
        return this.getEndIndicator().length
            && this.getEndIndicator().data('isLoading');
    }

    ActivityBox.prototype.initLoadMore = function () {
        if (!window.IntersectionObserver) {
            return;
        }

        const that = this;

        if (!that.getEndIndicator().length) {
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            if (that.isLoading()) {
                return;
            }

            if (entries.length && entries[0].isIntersecting) {
                that.loadMore();
            }
        }, {rootMargin: '1px'});

        observer.observe(that.getEndIndicator()[0]);
    }

    ActivityBox.prototype.loadMore = function () {
        const endIndicator = this.getEndIndicator();
        const lastActivity = endIndicator.prev('[data-activity-id]');

        if (lastActivity.length === 0) {
            return;
        }

        endIndicator.data('isLoading', true);
        loader.append(endIndicator);

        const data = {lastActivityId: lastActivity.data('activity-id')};

        client.get(this.$.data('box-url'), {data}).then(function (response) {
            for (const id in response.activities) {
                endIndicator.before(response.activities[id]);
            }

            if (response.isLast) {
                // Remove the end indicator because no more activities to load
                endIndicator.remove();
            }
        }).catch(function(err) {
            module.log.error(err, true);
        }).finally(function() {
            loader.reset(endIndicator);
            endIndicator.data('isLoading', false);
        });
    }

    const unload = function () {
        const activityBox = Widget.instance('[data-ui-widget="activity.ActivityBox"]');
        if (activityBox && activityBox.$) {
            // Cleanup nicescroll rails from dom
            activityBox.$.getNiceScroll().remove();
        }
    }

    module.export({
        initOnPjaxLoad: true,
        ActivityBox,
        unload,
    });
});
