humhub.module('ui.topNavigation', function (module, require, $) {
    var $topBarSecond = $('#topbar-second');
    var $topNav = $('#top-menu-nav');
    var $topSub = $('#top-menu-sub');

    var init = function () {
        $(window).on('resize', rebuild);

        setTimeout(function () {
            fixNavigationOverflow();
            // Until now the second row of wrapped items is clipped by CSS
            $topBarSecond.addClass('top-nav-ready');
        }, 50);

        // Late loaded (theme) web fonts change the item widths after the first calculation
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(rebuild);
        }
    };

    var rebuild = function () {
        // Prevent overflow on to start with
        $topBarSecond.css('overflow', 'hidden');
        // Start with hidden pulldown menu
        $topSub.hide();
        // Bring items in the MenuDropdown back in the menu
        $('#top-menu-sub-dropdown').children().appendTo('#top-menu-nav').find('i').after('<br>');
        // move pulldown menu to last position
        $topNav.append($topSub);

        fixNavigationOverflow();
    };

    var fixNavigationOverflow = function () {
        if (!isOverflow()) {
            $topBarSecond.css('overflow', '');
            return;
        }

        var $topMenuDropdown = $('#top-menu-sub-dropdown');
        if (!$topMenuDropdown.length) {
            // Without the sub menu no item can be moved, the loop below would never end
            $topBarSecond.css('overflow', '');
            return;
        }

        $topSub.show();

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
