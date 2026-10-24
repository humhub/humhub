humhub.module('user', function (module, require, $) {
    var event = require('event');

    var isGuest = function () {
        return module.config.isGuest;
    };

    var guid = function () {
        return module.config.guid;
    };

    var getLocale = function () {
        return module.config.locale;
    };

    /**
     * Keeps the follower counters of a user (`[data-user-follower-count="<user id>"]`, the
     * profile header's, see ProfileHeaderCounterSet) current when a follow button of the page
     * (the UserFollowButton island) followed or unfollowed that user.
     *
     * @since 1.20
     */
    var updateFollowerCount = function (evt, payload) {
        if (!payload || payload.followerCount === null || payload.followerCount === undefined) {
            return;
        }

        var count = Number(payload.followerCount);
        var text = String(count);
        if (count >= 1000) {
            // Short as the server renders it (1.2K); the plain number for a locale Intl rejects.
            try {
                text = new Intl.NumberFormat(String(getLocale() || 'en').replace('_', '-'), {notation: 'compact', maximumFractionDigits: 1}).format(count);
            } catch (e) {
                text = String(count);
            }
        }

        $('[data-user-follower-count="' + Number(payload.userId) + '"]').find('.count').text(text);
    };

    var init = function () {
        event.on('user:follow-changed', updateFollowerCount);
    };

    module.export({
        init: init,
        isGuest: isGuest,
        guid: guid,
        getLocale: getLocale
    });
});
