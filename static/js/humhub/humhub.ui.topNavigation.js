humhub.module('ui.topNavigation', function (module, require, $) {
    var $topBarSecond = $('#topbar-second');
    var $topNav = $('#top-menu-nav');
    var $topSub = $('#top-menu-sub');

    var init = function () {
        $(window).on('resize', function () {
            // Prevent overflow on to start with
            $topBarSecond.css('overflow', 'hidden');
            // Start with hidden pulldown menu
            $topSub.hide();
            // Bring items in the MenuDropdown back in the menu
            $('#top-menu-sub-dropdown').children().appendTo('#top-menu-nav').find('i').after('<br>');
            // move pulldown menu to last position
            $topNav.append($topSub);

            fixNavigationOverflow();
        });

        setTimeout(fixNavigationOverflow, 50);
    };

    var fixNavigationOverflow = function () {
        if (!isOverflow()) {
            $topBarSecond.css('overflow', '');
            return;
        }

        $topSub.show();
        var $topMenuDropdown = $('#top-menu-sub-dropdown');

        while (isOverflow() && moveNextItemToDropDown($topMenuDropdown)) {
        }

        $topBarSecond.css('overflow', '');
        $topSub.find('.dropdown-toggle').dropdown();
    };

    var moveNextItemToDropDown = function ($topMenuDropdown) {
        var $item = $topNav.children('.top-menu-item:last');
        if (!$item.length) {
            return false;
        }

        $item.find('br').remove();
        $topMenuDropdown.prepend($item);
        return true;
    };

    var isOverflow = function () {
        var $searchMenu = $('.search-menu');
        return ($topNav.length && $topBarSecond.length && $topNav[0].offsetHeight > $topBarSecond[0].offsetHeight)
            || ($searchMenu.length && $searchMenu[0].offsetTop);
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
