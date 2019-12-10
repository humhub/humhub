humhub.module('ui.view', function (module, require, $) {
    var title;
    var state = {};

    var isSmall = function () {
        return module.getWidth() <= 767;
    };

    var isMedium = function () {
        return module.getWidth() > 767 && module.getWidth() <= 991;
    };

    var isNormal = function () {
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

        if(isSmall() && module.config.useDefaultSwipe) {
            setTimeout(initMobileSidebar, 50);
        }

        module.log.debug('Current view state', state);
    };

    var initMobileSidebar = function() {
        var duration = 500;
        var animation = 'swing';

        var topPadding = $('#topbar-first').length ? $('#topbar-first').height() : 0;
        topPadding += $('#topbar-second').length ? $('#topbar-second').height() : 0;
        topPadding += $('.space-nav').find('.container-fluid').length ? $('.space-nav').height() : 0;
        topPadding +=  7;

        $('.layout-sidebar-container').css({
            'position': 'fixed',
            'padding': topPadding + 'px 5px 5px 5px',
            'top' : '0',
            'width': '100%',
            'height': '100%',
            'background': 'white',
            'left': '100%',
            'overflow-y': 'auto',
            'z-index' : '997'
        });

        $(document).on('swiped-left', function(e) {
            $('.layout-sidebar-container').css({height: '100%'});
            $('.layout-sidebar-container').show().animate({'left' : '0'}, {
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
            /*$('.layout-sidebar-container').show().animate({'left' : '0'}, 250,  function() {
                $('.layout-content-container').hide();
            });*/


        });

        $(document).on('swiped-right', function(e) {
            $('.layout-content-container').show();

            $('.layout-sidebar-container').animate({'left' : '100%'}, {
                step: function (now, fx) {
                    $(this).css({"transform": "translate3d("+now+"px, 0px, 0px)"});
                },
                duration: duration,
                easing: animation,
                queue: false,
                complete: function () {
                    $('.layout-sidebar-container')[0].scrollTo(0, 0);
                    $('.layout-sidebar-container').hide();
                    $('body').removeClass('modal-open');
                }
            }, 'linear');

            /*$('.layout-sidebar-container').animate({'left' : '100%'}, 250, function() {
                $('.layout-sidebar-container')[0].scrollTo(0, 0);
                $('.layout-sidebar-container').hide();
            });*/
        });

    }

    module.export({
        init: init,
        isSmall: isSmall,
        isMedium: isMedium,
        isNormal: isNormal,
        getHeight: getHeight,
        getWidth: getWidth,
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
