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
     * Focuses the first shown control inside an element, else the element itself.
     *
     * @param {Element|jQuery} el
     * @returns {boolean} whether the focus was moved
     */
    var focusInto = function (el) {
        el = element(el);
        if (!el) {
            return false;
        }
        var control = $(el).find(CONTROLS).filter(function () {
            return isFocusable(this);
        })[0];
        return focus(control) || focus(el);
    };

    /**
     * Focuses the content container, the target the focus takes after a pjax swap as well.
     *
     * @returns {boolean} whether the focus was moved
     */
    var focusContent = function () {
        var target = null;
        CONTENT.some(function (selector) {
            target = document.querySelector(selector);
            return !!target;
        });
        return focus(target);
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
     * inside the element, already lost to <body>, or in a dialog that is closing (the confirm).
     *
     * @param {Element|jQuery} el
     * @param {string} selector
     * @param {Element|jQuery} fallback
     * @returns {boolean} whether the focus was moved
     */
    var handOver = function (el, selector, fallback) {
        var $el = $(el);
        if (!$el.length) {
            return false;
        }
        var active = document.activeElement;
        var inClosingDialog = !!active.closest('.modal') && !active.closest('.modal.show');
        if (active !== document.body && !$el[0].contains(active) && !inClosingDialog) {
            return false;
        }
        var shown = function () {
            return isFocusable(this);
        };
        var $next = $el.nextAll(selector).filter(shown).first();
        var $prev = $el.prevAll(selector).filter(shown).first();
        return focusInto($next[0]) || focusInto($prev[0]) || focus(fallback) || focusContent();
    };

    module.export({
        isFocusable: isFocusable,
        focus: focus,
        focusInto: focusInto,
        focusContent: focusContent,
        restore: restore,
        handOver: handOver,
    });
});
