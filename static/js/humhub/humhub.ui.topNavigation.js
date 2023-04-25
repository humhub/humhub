humhub.module('ui.topNavigation', function (module, require, $) {
    var $topBarSecond = $('#topbar-second');
    var $topNav = $('#top-menu-nav');

    var init = function () {
        $(window).on('resize', function () {
            // Prevent overflow on to start with
            $topBarSecond.css('overflow', 'hidden');
            // Start with hidden pulldown menu
            $('#top-menu-sub').hide();
            // Bring items in the MenuDropdown back in the menu
            $('#top-menu-sub-dropdown').children().appendTo('#top-menu-nav');
            // move pulldown menu to last position
            $topNav.append($('#top-menu-sub'));

            fixNavigationOverflow();
        });

        setTimeout(fixNavigationOverflow, 50);
    };

    var fixNavigationOverflow = function () {
        if (!isOverflow()) {
            $topBarSecond.css('overflow', '');
            return;
        }

        $('#top-menu-sub').show();
        var $topMenuDropdown = $('#top-menu-sub-dropdown');

        while (isOverflow() && moveNextItemToDropDown($topMenuDropdown)) {}

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
        $searchMenu = $('.search-menu');
        return $topNav[0].offsetHeight > $topBarSecond[0].offsetHeight || ($searchMenu.length && $searchMenu[0].offsetTop);
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});

