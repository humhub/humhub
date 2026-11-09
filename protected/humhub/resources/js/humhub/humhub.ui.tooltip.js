/**
 * Bootstrap tooltips on `.tt` and `[data-bs-toggle="tooltip"]`, bound by delegation so elements
 * added later get one too. A tooltip shows on hover, and on focus when the focus moves by keyboard
 * (`ui.appearance.keyboardModality()`), not after a click. It hides when the focus leaves and on
 * Escape, which then closes nothing else (docs/ui/keyboard.md, docs/ui/tooltip.md).
 *
 * @since 1.20
 */
humhub.module('ui.tooltip', function (module, require, $) {
    var appearance = require('ui.appearance');

    var SELECTOR = '[data-bs-toggle="tooltip"], .tt';

    var target = function (node) {
        return node && typeof node.closest === 'function' ? node.closest(SELECTOR) : null;
    };

    var get = function (el) {
        return el ? bootstrap.Tooltip.getInstance(el) : null;
    };

    var create = function (el) {
        return bootstrap.Tooltip.getOrCreateInstance(el, {trigger: 'hover'});
    };

    var isShown = function (tooltip) {
        return tooltip._isShown();
    };

    /**
     * @returns {boolean} whether any tooltip was shown
     */
    var hideAll = function () {
        var hidden = false;
        // Bootstrap marks any element whose tooltip is shown with aria-describedby, so tooltips
        // created by other modules outside the selector are dismissed too
        document.querySelectorAll(SELECTOR + ', [aria-describedby]').forEach(function (el) {
            var tooltip = get(el);
            if (tooltip && isShown(tooltip)) {
                tooltip.hide();
                hidden = true;
            }
        });
        return hidden;
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
            var el = target(e.target);
            if (el && appearance.keyboardModality()) {
                create(el).show();
            }
        });

        document.addEventListener('focusout', function (e) {
            var tooltip = get(target(e.target));
            if (tooltip) {
                tooltip.hide();
            }
        });

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
