humhub.module('ui.topNavigation', function (module, require, $) {
    var $topBarSecond = $('#topbar-second');
    var $topNav = $topBarSecond.find('#top-menu-nav');

    // Prevent overflow on init
    $topBarSecond.css('overflow', 'hidden');

    var init = function () {
        $(window).on('resize', function () {
            fixNavigationOverflow();
        });

        if (!isOverflow()) {
            $topBarSecond.css('overflow', '');
            return;
        }

        setTimeout(fixNavigationOverflow, 50);
    };

    var fixNavigationOverflow = function () {
        if (!isOverflow()) {
            return;
        }

        var $topMenuDropdown = $topNav.find('#top-menu-sub').show().find('#top-menu-sub-dropdown');

        while (isOverflow() && moveNextItemToDropDown($topMenuDropdown)) {}

        // We remove the next dropdown for edgecases, e.g. the scrollbar appears after init
        moveNextItemToDropDown($topMenuDropdown);

        $topBarSecond.css('overflow', '');
        $('#top-menu-sub').find('.dropdown-toggle').dropdown();

    };

    var moveNextItemToDropDown = function($topMenuDropdown)
    {
        var $item = $topNav.children('.top-menu-item:last');
        if(!$item.length) {
            return false;
        }

        $item.find('br').remove();
        $topMenuDropdown.prepend($item);
        return true;
    };

    var isOverflow = function() {
        return $topNav[0].offsetHeight > $topBarSecond[0].offsetHeight ||  $('.search-menu')[0].offsetTop;
    };

    module.export({
        init: init,
        sortOrder: 100,
    })
});
