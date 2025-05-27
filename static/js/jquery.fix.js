(function($) {
    // Save original methods
    $.fn.originalHide = $.fn.hide;
    $.fn.originalShow = $.fn.show;
    $.fn.originalToggle = $.fn.toggle;
    $.fn.originalFadeIn = $.fn.fadeIn;
    $.fn.originalFadeOut = $.fn.fadeOut;
    $.fn.originalFadeToggle = $.fn.fadeToggle;
    $.fn.originalSlideDown = $.fn.slideDown;
    $.fn.originalSlideUp = $.fn.slideUp;
    $.fn.originalSlideToggle = $.fn.slideToggle;

    $.fn.hide = function() {
        return this.addClass('d-none');
    };

    $.fn.show = function() {
        return this.removeClass('d-none');
    };

    $.fn.toggle = function(display) {
        return this.toggleClass('d-none', typeof display === 'boolean' ? !display : display);
    };

    $.fn.fadeIn = function(duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function() {
            const $el = $(this);
            $el.removeClass('d-none').css({opacity: 0, display: ''});
            $el.animate({ opacity: 1 }, duration, easing, function() {
                $el.css('opacity', '');
                if (typeof complete === 'function') {
                    complete.call(this);
                }
            });
        });
    };

    $.fn.fadeOut = function(duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function() {
            const $el = $(this);
            $el.originalFadeOut(duration, easing, function() {
                $el.addClass('d-none').css('display', '');
                if (typeof complete === 'function') {
                    complete.call(this);
                }
            });
        });
    };

    $.fn.fadeToggle = function(duration, easing, complete) {
        return this.each(function() {
            const $el = $(this);

            if ($el.hasClass('d-none')) {
                $el.fadeIn(duration, easing, complete);
            } else {
                $el.fadeOut(duration, easing, complete);
            }
        });
    };

    $.fn.slideDown = function(duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function() {
            const $el = $(this);

            $el.removeClass('d-none').css({
                display: '',
                overflow: 'hidden',
                height: 0
            });

            const fullHeight = $el.get(0).scrollHeight;

            $el.animate({ height: fullHeight }, duration, easing, function() {
                $el.css({
                    height: '',
                    overflow: ''
                });

                if (typeof complete === 'function') {
                    complete.call(this);
                }
            });
        });
    };

    $.fn.slideUp = function(duration, easing, complete) {
        [duration, easing, complete] = normalizeArgs(duration, easing, complete);

        return this.each(function() {
            const $el = $(this);
            $el.originalSlideUp(duration, easing, function() {
                $el.addClass('d-none').css('display', '');
                if (typeof complete === 'function') {
                    complete.call(this);
                }
            });
        });
    };

    $.fn.slideToggle = function(duration, easing, complete) {
        return this.each(function() {
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
    }
})(jQuery);
