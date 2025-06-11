/**
 * jQuery Fix to work with Bootstrap 5
 *
 * @deprecated since 1.18
 *
 * TODO: Remove when jQuery is no longer supported
 */

(function ($) {
    $.fn.bs5FixHide = $.fn.hide;
    $.fn.bs5FixShow = $.fn.show;
    $.fn.bs5FixToggle = $.fn.toggle;
    $.fn.bs5FixFadeIn = $.fn.fadeIn;
    $.fn.bs5FixFadeOut = $.fn.fadeOut;
    $.fn.bs5FixFadeToggle = $.fn.fadeToggle;
    $.fn.bs5FixSlideDown = $.fn.slideDown;
    $.fn.bs5FixSlideUp = $.fn.slideUp;
    $.fn.bs5FixSlideToggle = $.fn.slideToggle;

    $.each(['hide', 'show', 'toggle'], function (_i, name) {
        $.fn[name] = function (speed, easing, callback) {
            const [duration, easingFinal, complete] = normalizeArgs(speed, easing, callback);

            if (duration != null) {
                if (name === 'hide') return this.fadeOut(duration, easingFinal, complete);
                if (name === 'show') return this.fadeIn(duration, easingFinal, complete);
                if (name === 'toggle') return this.fadeToggle(duration, easingFinal, complete);
            }

            return this.each(function () {
                const $el = $(this);
                if (name === 'hide' || (name === 'toggle' && $el.is(':visible'))) {
                    $el.addClass('d-none').removeClass('d-revert');
                } else if (name === 'show' || (name === 'toggle' && !$el.is(':visible'))) {
                    $el.removeClass('d-none');
                    if ($el.css('display') === 'none') {
                        $el.addClass('d-revert');
                    }
                }

                if (typeof complete === 'function') complete.call(this);
            });
        };
    });

    $.fn.fadeIn = function (duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function () {
            const $el = $(this);
            if ($el.is(':visible')) {
                return;
            }
            $el.stop(true, true)
                .css({ opacity: 0, display: '' })
                .removeClass('d-none')
                .animate({ opacity: 1 }, duration, easing, function () {
                    $el.css('opacity', '');
                    if (typeof complete === 'function') complete.call(this);
                });
        });
    };

    $.fn.fadeOut = function (duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function () {
            const $el = $(this);
            if (!$el.is(':visible')) {
                return;
            }
            $el.stop(true, true).animate({ opacity: 0 }, duration, easing, function () {
                $el.addClass('d-none').css('opacity', '');
                if (typeof complete === 'function') complete.call(this);
            });
        });
    };

    $.fn.fadeToggle = function (duration, easing, complete) {
        return this.each(function () {
            const $el = $(this);
            if ($el.hasClass('d-none')) {
                $el.fadeIn(duration, easing, complete);
            } else {
                $el.fadeOut(duration, easing, complete);
            }
        });
    };

    $.fn.slideDown = function (duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function () {
            const $el = $(this);

            if (!$el.hasClass('d-none')) {
                return;
            }

            $el.removeClass('d-none').css({
                overflow: 'hidden',
                height: 0,
                display: ''
            });

            const fullHeight = this.scrollHeight;

            $el.stop(true, true).animate({ height: fullHeight }, duration, easing, function () {
                $el.css({ height: '', overflow: '' });
                if (typeof complete === 'function') complete.call(this);
            });
        });
    };

    $.fn.slideUp = function (duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function () {
            const $el = $(this);
            const currentHeight = $el.outerHeight();

            $el.css({
                overflow: 'hidden',
                height: currentHeight
            });

            $el.stop(true, true).animate({ height: 0 }, duration, easing, function () {
                $el.addClass('d-none').css({ height: '', overflow: '' });
                if (typeof complete === 'function') complete.call(this);
            });
        });
    };

    $.fn.slideToggle = function (duration, easing, complete) {
        return this.each(function () {
            const $el = $(this);
            if ($el.hasClass('d-none')) {
                $el.slideDown(duration, easing, complete);
            } else {
                $el.slideUp(duration, easing, complete);
            }
        });
    };

    const normalizeArgs = function (duration, easing, complete) {
        if (typeof duration === 'function') {
            complete = duration;
            duration = undefined;
            easing = undefined;
        } else if (typeof easing === 'function') {
            complete = easing;
            easing = undefined;
        }
        return [duration, easing, complete];
    };
})(jQuery);
