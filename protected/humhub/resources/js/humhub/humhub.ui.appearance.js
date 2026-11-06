/**
 * Colour mode and accessibility options of the current user, as set on the `<html>` element by
 * `\humhub\components\Appearance`. "System" options have no attribute and follow the media query.
 *
 * @since 1.20
 */
humhub.module('ui.appearance', function (module, require, $) {
    var root = document.documentElement;

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

    var init = function () {
        // jQuery animations (fades, slides) finish at once
        $.fx.off = reducedMotion();
    };

    module.export({
        init: init,
        colorMode: colorMode,
        reducedMotion: reducedMotion,
        highContrast: highContrast,
    });
});
