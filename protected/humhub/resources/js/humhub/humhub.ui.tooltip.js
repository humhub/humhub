/**
 * Bootstrap tooltips on `.tt` and `[data-bs-toggle="tooltip"]`, bound by delegation so elements
 * added later get one too. A tooltip shows on hover, and on focus when the focus moves by keyboard
 * (`ui.appearance.keyboardModality()`), not after a click; a link or button shows the tooltip of the
 * one trigger inside it (the date in a permalink). It stays while the pointer is on it, and hides
 * when the focus leaves and on Escape, which then closes nothing else (WCAG 1.4.13,
 * docs/ui/keyboard.md, docs/ui/tooltip.md).
 *
 * @since 1.20
 */
humhub.module('ui.tooltip', function (module, require, $) {
    var appearance = require('ui.appearance');

    var SELECTOR = '[data-bs-toggle="tooltip"], .tt';

    // The time to move the pointer from the trigger onto its tooltip
    var HIDE_DELAY = 150;

    // Set while a tooltip is hidden whatever the pointer is on: Escape, a click
    var dismissing = false;

    var target = function (node) {
        return node && typeof node.closest === 'function' ? node.closest(SELECTOR) : null;
    };

    /**
     * The trigger a focused element shows: itself or its ancestor, or the one trigger inside a link or button
     */
    var focusTarget = function (node) {
        var el = target(node);
        if (el || !node || typeof node.matches !== 'function' || !node.matches('a[href], button')) {
            return el;
        }

        var inner = node.querySelectorAll(SELECTOR);
        return inner.length === 1 ? inner[0] : null;
    };

    var get = function (el) {
        return el ? bootstrap.Tooltip.getInstance(el) : null;
    };

    var create = function (el) {
        return bootstrap.Tooltip.getOrCreateInstance(el, {trigger: 'hover', delay: {show: 0, hide: HIDE_DELAY}});
    };

    var isShown = function (tooltip) {
        return tooltip._isShown();
    };

    /**
     * @returns {boolean} whether any tooltip was shown
     */
    var hideAll = function () {
        var hidden = false;
        dismissing = true;
        // Bootstrap marks any element whose tooltip is shown with aria-describedby, so tooltips
        // created by other modules outside the selector are dismissed too
        document.querySelectorAll(SELECTOR + ', [aria-describedby]').forEach(function (el) {
            var tooltip = get(el);
            if (tooltip && isShown(tooltip)) {
                tooltip.hide();
                hidden = true;
            }
        });
        dismissing = false;
        return hidden;
    };

    /**
     * Keeps a tooltip while the pointer is on it (hoverable, WCAG 1.4.13): Bootstrap hides it once the
     * pointer left its trigger. It goes when the pointer leaves it for somewhere else than the trigger.
     */
    var keepWhileHovered = function (e) {
        var trigger = e.target;
        var tooltip = get(trigger);
        var tip = tooltip && tooltip.tip;
        if (dismissing || !tip || !tip.matches(':hover')) {
            return;
        }

        e.preventDefault();
        if (!tip.dataset.hhKeep) {
            tip.dataset.hhKeep = '1';
            tip.addEventListener('mouseleave', function () {
                delete tip.dataset.hhKeep;
                if (!trigger.matches(':hover')) {
                    tooltip.hide();
                }
            }, {once: true});
        }
    };

    /**
     * Removes tooltip elements whose trigger left the document (replaced by ajax, or removed while
     * it was focused, which fires no focusout), as the old click handler swept every tooltip away
     */
    var removeOrphans = function () {
        document.querySelectorAll('.tooltip').forEach(function (tip) {
            if (!tip.id || !document.querySelector('[aria-describedby~="' + tip.id + '"]')) {
                tip.remove();
            }
        });
    };

    var init = function () {
        // The first hover happened before Bootstrap could listen, so show it by hand once
        document.addEventListener('mouseover', function (e) {
            var el = target(e.target);
            if (el && !get(el)) {
                create(el).show();
            }
        });

        // Shown at once, without Bootstrap's hover delay
        document.addEventListener('focusin', function (e) {
            var el = focusTarget(e.target);
            if (el && appearance.keyboardModality()) {
                create(el).show();
            }
        });

        document.addEventListener('focusout', function (e) {
            var tooltip = get(focusTarget(e.target));
            if (tooltip) {
                tooltip.hide();
            }
        });

        document.addEventListener('hide.bs.tooltip', keepWhileHovered);

        // Capturing, so Escape dismisses any visible tooltip first (WCAG 1.4.13) and then closes nothing else
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && hideAll()) {
                e.stopPropagation();
            }
        }, true);

        // A click dismisses every tooltip, as the old ui.additions handler did, but through Bootstrap so its state stays right
        document.addEventListener('click', function () {
            hideAll();
            removeOrphans();
        });
    };

    module.export({
        init: init,
        hideAll: hideAll,
    });
});
