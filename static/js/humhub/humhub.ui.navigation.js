humhub.module('ui.navigation', function (module, require, $) {

    var init = function () {
        const navElements = '#top-menu-nav a.nav-link';
        $(navElements + ':not(#space-menu):not(#top-dropdown-menu):not([data-action-click="ui.modal.load"])').on('click', function () {
            $(navElements).removeClass('active');
            $(this).addClass('active');
        });
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
