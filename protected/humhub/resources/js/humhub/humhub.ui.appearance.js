/**
 * Colour mode and accessibility options of the current user, as set on the `<html>` element by
 * `\humhub\components\Appearance`. "System" options have no attribute and follow the media query.
 *
 * Also keeps `data-hh-input-modality` on `<html>`: `keyboard` once Tab moves the focus, `pointer` after a
 * click or tap. The CSS draws the focus ring on a text field by keyboard only (`:focus-visible` matches a
 * clicked field too). Only Tab counts: typing or the arrow keys in a clicked field is still pointer use.
 *
 * @since 1.20
 */
humhub.module('ui.appearance', function (module, require, $) {
    var root = document.documentElement;

    var setInputModality = function (modality) {
        if (root.getAttribute('data-hh-input-modality') !== modality) {
            root.setAttribute('data-hh-input-modality', modality);
        }
    };

    var matches = function (query) {
        return typeof window.matchMedia === 'function' && window.matchMedia(query).matches;
    };

    /**
     * @returns {string} `light` or `dark`
     */
    var colorMode = function () {
        return root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    };

    var reducedMotion = function () {
        var motion = root.getAttribute('data-hh-motion');
        return motion ? motion === 'reduce' : matches('(prefers-reduced-motion: reduce)');
    };

    var highContrast = function () {
        var contrast = root.getAttribute('data-hh-contrast');
        return contrast ? contrast === 'more' : matches('(prefers-contrast: more)');
    };

    /**
     * @returns {boolean} whether the focus is moved by keyboard
     */
    var keyboardModality = function () {
        return root.getAttribute('data-hh-input-modality') === 'keyboard';
    };

    var init = function () {
        // jQuery animations (fades, slides) finish at once
        $.fx.off = reducedMotion();

        // Capturing, so a handler stopping the event can't leave the modality stale
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Tab') {
                setInputModality('keyboard');
            }
        }, true);
        document.addEventListener('pointerdown', function () {
            setInputModality('pointer');
        }, true);
    };

    module.export({
        init: init,
        colorMode: colorMode,
        reducedMotion: reducedMotion,
        highContrast: highContrast,
        keyboardModality: keyboardModality,
    });
});
