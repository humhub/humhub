/**
 * The keys of a feed (docs/ui/keyboard.md, "Feeds"): a list with `role="feed"` whose entries are
 * `role="article"`. PageDown and PageUp move the focus between the entries, Ctrl+End and Ctrl+Home
 * leave the feed, and at its end PageDown asks for more (`humhub:feed:more`) and follows to the new
 * entry once the feed is no longer `aria-busy`. The innermost feed around the focus that has entries
 * takes the keys; a text field or editor keeps them.
 *
 * @since 1.20
 */
humhub.module('ui.feed', function (module, require, $) {
    var focus = require('ui.focus');

    var FEED = '[role="feed"]';
    var ARTICLE = '[role="article"]';
    var EDITABLE = 'input, textarea, select, [contenteditable]:not([contenteditable="false"])';
    var CONTROLS = 'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"]), '
        + '[contenteditable]:not([contenteditable="false"])';

    /**
     * Dispatched on the feed element (bubbling, `detail.direction` is `next` or `prev`) when PageDown
     * or PageUp finds no further entry. A feed that then loads sets `aria-busy="true"` and removes it
     * once the entries are in the document; the focus follows to the new entry.
     */
    var EVENT_MORE = 'humhub:feed:more';

    /**
     * @returns {boolean} whether `b` comes after `a` in the document
     */
    var follows = function (a, b) {
        return !!(a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING);
    };

    /**
     * The shown entries of a feed, without those of a feed nested in it.
     *
     * @param {Element} feed
     * @returns {Element[]}
     */
    var articles = function (feed) {
        return $(feed).find(ARTICLE).filter(function () {
            return this.parentElement.closest(FEED) === feed && focus.isFocusable(this);
        }).get();
    };

    /**
     * The entry of the feed around an element (an article in the content of an entry is none of them).
     */
    var articleOf = function (feed, el) {
        var list = articles(feed);
        return $(el).parents(ARTICLE).addBack(ARTICLE).filter(function () {
            return list.indexOf(this) !== -1;
        })[0] || null;
    };

    /**
     * The entry to move to: the next (`dir` 1) or previous (`dir` -1) one after the entry around
     * `from`; when `from` is in no entry (a "Show more" link), the first entry after it or the last
     * one before it.
     *
     * @returns {Element|null}
     */
    var neighbour = function (feed, from, dir) {
        var list = articles(feed);
        var current = articleOf(feed, from);
        if (current) {
            var index = list.indexOf(current) + dir;
            return index >= 0 && index < list.length ? list[index] : null;
        }
        var candidates = list.filter(function (article) {
            return dir > 0 ? follows(from, article) : follows(article, from);
        });
        return (dir > 0 ? candidates[0] : candidates[candidates.length - 1]) || null;
    };

    /**
     * Focuses an entry and scrolls it to the top of the window, but only when it is not fully in view: while it
     * is, the focus ring shows the position and the page does not jump.
     */
    var focusEntry = function (article) {
        if (!focus.focus(article)) {
            return false;
        }
        var rect = article.getBoundingClientRect();
        var top = parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
        if (rect.top < top || rect.bottom > window.innerHeight) {
            article.scrollIntoView({block: 'start'});
        }
        return true;
    };

    /**
     * Asks the feed for more entries and, once it is no longer busy, moves on from `from` - unless
     * the focus went elsewhere meanwhile.
     */
    var more = function (feed, from, dir) {
        feed.dispatchEvent(new CustomEvent(EVENT_MORE, {
            bubbles: true,
            detail: {direction: dir > 0 ? 'next' : 'prev'},
        }));

        if (feed.getAttribute('aria-busy') !== 'true') {
            return;
        }

        var observer = new MutationObserver(function () {
            if (feed.getAttribute('aria-busy') === 'true') {
                return;
            }
            observer.disconnect();
            var active = document.activeElement;
            if (from.isConnected && active && (active === from || from.contains(active))) {
                var next = neighbour(feed, from, dir);
                if (next) {
                    focusEntry(next);
                }
            }
        });
        observer.observe(feed, {attributes: true, attributeFilter: ['aria-busy']});
    };

    /**
     * PageDown or PageUp: to the next or previous entry, at the end a request for more.
     *
     * @returns {boolean} whether the focus was moved
     */
    var move = function (feed, from, dir) {
        var next = neighbour(feed, from, dir);
        if (next) {
            return focusEntry(next);
        }
        more(feed, articleOf(feed, from) || from, dir);
        return false;
    };

    /**
     * Ctrl+End or Ctrl+Home: to the first control after the feed or the last one before it.
     *
     * @returns {boolean} whether the focus was moved
     */
    var leave = function (feed, dir) {
        var list = $(document.body).find(CONTROLS).filter(function () {
            return !feed.contains(this) && focus.isFocusable(this)
                && (dir > 0 ? follows(feed, this) : follows(this, feed));
        }).get();
        if (dir < 0) {
            list.reverse();
        }
        return list.some(function (control) {
            return focus.focus(control);
        });
    };

    var onKeydown = function (evt) {
        if (evt.isDefaultPrevented() || evt.altKey || evt.shiftKey || $(evt.target).closest(EDITABLE).length) {
            return;
        }
        var modifier = evt.ctrlKey || evt.metaKey;
        var paging = !modifier && (evt.key === 'PageDown' || evt.key === 'PageUp');
        var leaving = modifier && (evt.key === 'End' || evt.key === 'Home');
        if (!paging && !leaving) {
            return;
        }
        if (!articles(this).length) {
            return; // an empty feed leaves the keys to the feed around it
        }
        // This feed owns the keys: the one around it must not act on them as well
        evt.stopPropagation();

        var dir = (evt.key === 'PageDown' || evt.key === 'End') ? 1 : -1;
        if (paging ? move(this, evt.target, dir) : leave(this, dir)) {
            evt.preventDefault();
        }
    };

    var init = function () {
        $(document).on('keydown.humhub-ui-feed', FEED, onKeydown);
    };

    module.export({
        EVENT_MORE: EVENT_MORE,
        init: init,
        articles: articles,
    });
});
