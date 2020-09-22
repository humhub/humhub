humhub.module('ui.view', function (module, require, $) {
    var object = require('util.object');

    var title;
    var state = {};

    var prevSwipeDelay = false;
    var prevSwipe = false;

    var scrollTimeout;

    var isSmall = function () {
        return module.getWidth() <= 767;
    };

    var isMedium = function () {
        return module.getWidth() > 767 && module.getWidth() <= 991;
    };

    /**
     * @deprecated since v1.5
     */
    var isNormal = function () {
        return isLarge();
    };

    var isLarge = function () {
        return module.getWidth() >= 991;
    };

    var setState = function (moduleId, controlerId, action) {
        state = {
            title: document.title,
            moduleId: moduleId,
            controllerId: controlerId,
            action: action
        };
    };

    var getHeight = function() {
        return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    };

    var getWidth = function() {
        return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    };

    module.initOnPjaxLoad = true;

    var init = function (pjax) {
        prevSwipeDelay = false;
        prevSwipe = false;
        $('body').removeClass('modal-open');

        if(isSmall() || isMedium() && module.config.useDefaultSwipe) {
            setTimeout(initMobileSidebar, 50);
        }

        module.log.debug('Current view state', state);
    };

    var isSwipeAllowed = function() {
        return !prevSwipeDelay && !prevSwipe;
    };

    var isActiveScroll = function() {
        return prevSwipeDelay;
    };

    var preventSwipe = function(prev) {
        prevSwipe = object.isDefined(prev) ? prev : true;
    };

    var initMobileSidebar = function() {

        var duration = 500;
        var animation = 'swing';
        var $sidebar = $('.layout-sidebar-container');

        $sidebar.css({
            'position': 'fixed',
            'top' : '0',
            'width': '100%',
            'height': '100%',
            'background': 'white',
            'left': '100%',
            'overflow-y': 'auto',
            'z-index' : '997'
        });

        window.addEventListener('scroll', function(){
            window.clearTimeout( scrollTimeout );
            prevSwipeDelay = true;

            scrollTimeout = setTimeout(function() {
                prevSwipeDelay = false;
            }, 400);
        }, true);

        $(document).on('swiped-left', function(e) {
            if(!isSwipeAllowed() || e.target && $(e.target).closest('[data-menu-id]').length) {
                return;
            }

            var topPadding = getContentTop() + 7;
            $sidebar.css({height: '100%', padding: topPadding + 'px 5px 5px 5px'})
                .show()
                .animate({'left' : '0'}, {
                    step: function (now, fx) {
                        $(this).css({"transform": "translate3d("+now+"px, 0px, 0px)"});
                    },
                    duration: duration,
                    easing: animation,
                    queue: false,
                    complete: function () {
                        $('body').addClass('modal-open');
                    }
                }, 'linear');
        });

        $(document).on('swiped-right', function(e) {
            $('.layout-content-container').show();

            $sidebar.animate({'left' : '100%'}, {
                step: function (now, fx) {
                    $(this).css({"transform": "translate3d("+now+"px, 0px, 0px)"});
                },
                duration: duration,
                easing: animation,
                queue: false,
                complete: function () {
                    $sidebar[0].scrollTo(0, 0);
                    $sidebar.hide();
                    $('body').removeClass('modal-open');
                }
            }, 'linear');
        });

    };

    var getContentTop = function() {
        var theme = require('ui.theme', true);

        if(object.isFunction(theme.getContentTop)) {
            return theme.getContentTop();
        }

        var $topBar = $('#topbar-second');

        return $topBar.position().top + $topBar.height();
    };

    module.export({
        init: init,
        sortOrder: 100,
        isSmall: isSmall,
        preventSwipe: preventSwipe,
        isActiveScroll: isActiveScroll,
        isMedium: isMedium,
        isNormal: isNormal,
        isLarge: isLarge,
        getHeight: getHeight,
        getWidth: getWidth,
        getContentTop: getContentTop,
        // This function is called by controller itself
        setState: setState,
        getState: function () {
            return $.extend({}, state);
        },
        getTitle: function () {
            return state.title;
        }
    });
});
