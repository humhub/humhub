/**
 * Keeps the keyboard focus from falling to <body>: hands it on when the element that holds it is
 * removed, and gives it back to the trigger when a dialog closes (docs/ui/keyboard.md, "Moving the
 * focus").
 *
 * @since 1.20
 */
humhub.module('ui.focus', function (module, require, $) {
    var FOCUSABLE = 'a[href], button, input, select, textarea, [tabindex]';
    var CONTROLS = 'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])';
    var CONTENT = ['.layout-content-container', '#main-content'];

    /**
     * The jQuery data key under which a dialog keeps the element to give the focus back to on close
     * (set by `ui.modal` on `show.bs.modal`, read by `restore()`, replaced by `handOver()` when the
     * trigger is removed along with the element it sits in).
     */
    var TRIGGER_KEY = 'hh-trigger';

    var element = function (el) {
        return el instanceof $ ? el[0] : (el || null);
    };

    var isShown = function (el) {
        if (typeof el.checkVisibility === 'function') {
            return el.checkVisibility({visibilityProperty: true});
        }
        return el.getClientRects().length > 0;
    };

    /**
     * @param {Element|jQuery} el
     * @returns {boolean} whether the element is in the document, shown and not disabled
     */
    var isFocusable = function (el) {
        el = element(el);
        return !!el && el.isConnected && el !== document.body && !el.disabled && isShown(el);
    };

    /**
     * Focuses an element. One that is no control becomes a programmatic focus target (`tabindex="-1"`).
     *
     * @param {Element|jQuery} el
     * @returns {boolean} whether the focus was moved
     */
    var focus = function (el) {
        el = element(el);
        if (!isFocusable(el)) {
            return false;
        }
        if (!el.matches(FOCUSABLE)) {
            el.setAttribute('tabindex', '-1');
        }
        el.focus({preventScroll: true});
        return document.activeElement === el;
    };

    /**
     * The first shown control inside an element, else the element itself when it can take the focus.
     *
     * @param {Element|jQuery} el
     * @returns {Element|null}
     */
    var firstControl = function (el) {
        el = element(el);
        if (!el) {
            return null;
        }
        var control = $(el).find(CONTROLS).filter(function () {
            return isFocusable(this);
        })[0];
        return control || (isFocusable(el) ? el : null);
    };

    /**
     * The content container, the target the focus takes after a pjax swap as well.
     *
     * @returns {Element|null}
     */
    var contentElement = function () {
        var target = null;
        CONTENT.some(function (selector) {
            target = document.querySelector(selector);
            return !!target;
        });
        return target;
    };

    /**
     * Focuses the content container.
     *
     * @returns {boolean} whether the focus was moved
     */
    var focusContent = function () {
        return focus(contentElement());
    };

    /**
     * Gives the focus back to the element that opened something, once it closed. A trigger inside a
     * closed dropdown hands over to the dropdown's toggle, a vanished trigger to the content. Nothing
     * happens when something outside `within` holds the focus meanwhile.
     *
     * @param {Element|jQuery} trigger
     * @param {Element|jQuery} within the element that closed
     * @returns {boolean} whether the focus was moved
     */
    var restore = function (trigger, within) {
        trigger = element(trigger);
        within = element(within);
        var active = document.activeElement;
        if (active && active !== document.body && !(within && within.contains(active))) {
            return false;
        }
        if (trigger && !isFocusable(trigger)) {
            var $menu = $(trigger).closest('.dropdown-menu');
            if ($menu.length) {
                trigger = $menu.parent().find('[data-bs-toggle="dropdown"]').first()[0] || trigger;
            }
        }
        return focus(trigger) || focusContent();
    };

    /**
     * Hands the focus of an element that is about to be removed to its next sibling matching
     * `selector`, else the previous one, else `fallback`, else the content. Only when the focus is
     * inside the element, already lost to <body>, or in a dialog, which then hands the focus over on
     * close: the dialog's trigger is replaced by the neighbour when it is gone or sits in the element.
     *
     * @param {Element|jQuery} el
     * @param {string} selector
     * @param {Element|jQuery} fallback
     * @returns {boolean} whether the focus was moved
     */
    var handOver = function (el, selector, fallback) {
        var $el = $(el);
        if (!$el.length || !$el[0].isConnected) {
            return false;
        }
        var active = document.activeElement;
        var $dialog = active ? $(active).closest('.modal') : $();
        if (active && active !== document.body && !$el[0].contains(active) && !$dialog.length) {
            return false;
        }

        var shown = function () {
            return isFocusable(this);
        };
        var target = firstControl($el.nextAll(selector).filter(shown).first()[0])
            || firstControl($el.prevAll(selector).filter(shown).first()[0])
            || (isFocusable(fallback) ? element(fallback) : null)
            || contentElement();

        if ($dialog.length) {
            // The focus stays in the dialog, open or closing; the dialog hands it over on close
            // (ui.modal's hidden handler) - to the neighbour when its own trigger is gone or part of
            // the removed element, else to its own trigger as before
            var trigger = element($dialog.data(TRIGGER_KEY));
            if (!trigger || !trigger.isConnected || $el[0].contains(trigger)) {
                $dialog.data(TRIGGER_KEY, target);
            }
            return false;
        }
        return focus(target);
    };

    module.export({
        TRIGGER_KEY: TRIGGER_KEY,
        isFocusable: isFocusable,
        focus: focus,
        focusContent: focusContent,
        restore: restore,
        handOver: handOver,
    });
});
