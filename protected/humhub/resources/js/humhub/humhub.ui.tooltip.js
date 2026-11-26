/**
 * Bootstrap tooltips on `.tt` and `[data-bs-toggle="tooltip"]`, bound by delegation so elements
 * added later get one too. A text cut off with an ellipsis (`data-tooltip="overflow"`) shows its full
 * text as tooltip, only while it is cut off (WCAG 1.4.12). A tooltip shows on hover, and on focus when the focus moves by keyboard
 * (`ui.appearance.keyboardModality()`), not after a click; a link or button shows the tooltip of the
 * one trigger inside it (the date in a permalink). One shows at a time. It stays while the pointer is on it, and hides
 * when the focus leaves and on Escape, which then closes nothing else (WCAG 1.4.13,
 * docs/ui/keyboard.md, docs/ui/tooltip.md). Scrolling closes it, except the tooltip of the keyboard focus.
 *
 * @since 1.20
 */
humhub.module('ui.tooltip', function (module, require, $) {
    var appearance = require('ui.appearance');

    var OVERFLOW = '[data-tooltip="overflow"]';
    var SELECTOR = '[data-bs-toggle="tooltip"], .tt, ' + OVERFLOW;

    // The time to move the pointer from the trigger onto its tooltip
    var HIDE_DELAY = 150;

    // Set while a tooltip is hidden whatever the pointer is on: Escape, a click
    var dismissing = false;

    var target = function (node) {
        return node && typeof node.closest === 'function' ? node.closest(SELECTOR) : null;
    };

    /**
     * The trigger a focused element shows: itself or its ancestor, or inside a link or button the text cut off
     * in it, else its one trigger
     */
    var focusTarget = function (node) {
        var el = target(node);
        if (el || !node || typeof node.matches !== 'function' || !node.matches('a[href], button')) {
            return el;
        }

        var inner = node.querySelectorAll(SELECTOR);
        var cutOff = Array.prototype.filter.call(inner, function (trigger) {
            return trigger.matches(OVERFLOW) && isCutOff(trigger);
        });
        if (cutOff.length === 1) {
            return cutOff[0];
        }
        return inner.length === 1 ? inner[0] : null;
    };

    var get = function (el) {
        return el ? bootstrap.Tooltip.getInstance(el) : null;
    };

    var create = function (el) {
        var config = {trigger: 'hover', delay: {show: 0, hide: HIDE_DELAY}};
        if (el.matches(OVERFLOW)) {
            // The text itself, its lines joined
            config.title = function () {
                return (this.innerText || this.textContent).replace(/\s+/g, ' ').trim();
            };
        }
        return bootstrap.Tooltip.getOrCreateInstance(el, config);
    };

    var isCutOff = function (el) {
        return el.scrollWidth > el.clientWidth + 1 || el.scrollHeight > el.clientHeight + 1;
    };

    /**
     * A text that is not cut off (any more, the window may have grown) shows no tooltip
     */
    var showOnlyWhenCutOff = function (e) {
        if (e.target.matches && e.target.matches(OVERFLOW) && !isCutOff(e.target)) {
            e.preventDefault();
        }
    };

    /**
     * One tooltip at a time: a tooltip that shows closes the others at once, also those still waiting out
     * their hide delay after the pointer moved on
     */
    var hideOthers = function (e) {
        if (!e.defaultPrevented) {
            hideAll(e.target, true);
        }
    };

    var isShown = function (tooltip) {
        return tooltip._isShown();
    };

    /**
     * @param {Element} [except] the trigger whose tooltip stays
     * @param {boolean} [instantly] without fading out
     * @returns {boolean} whether any tooltip was shown
     */
    var hideAll = function (except, instantly) {
        var hidden = false;
        dismissing = true;
        // Bootstrap marks any element whose tooltip is shown with aria-describedby, so tooltips
        // created by other modules outside the selector are dismissed too
        document.querySelectorAll(SELECTOR + ', [aria-describedby]').forEach(function (el) {
            var tooltip = get(el);
            if (el !== except && tooltip && isShown(tooltip)) {
                if (instantly && tooltip.tip) {
                    tooltip.tip.classList.remove('fade');
                }
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
     * Closes the tooltips when the page or a list scrolls, so none travels with its trigger out of the list.
     * The tooltip of the keyboard focus stays: Tab scrolls the focused control into view, the tooltip hides only
     * while that control is out of view (`data-popper-reference-hidden`, _tooltip.scss).
     */
    var hideOnScroll = function () {
        if (!document.querySelector('.tooltip.show')) {
            return;
        }
        hideAll(appearance.keyboardModality() ? focusTarget(document.activeElement) : null, true);
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
        document.addEventListener('show.bs.tooltip', showOnlyWhenCutOff);
        // After the one above, which may cancel the showing
        document.addEventListener('show.bs.tooltip', hideOthers);

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

        // Capturing, as the scroll of a list does not bubble
        document.addEventListener('scroll', hideOnScroll, {capture: true, passive: true});
    };

    module.export({
        init: init,
        hideAll: hideAll,
    });
});
