humhub.module('ui.navigation', function (module, require, $) {

    var event = require('event');

    var init = function () {
        module.initTopNav();
    };

    var initTopNav = function () {
        event.on('humhub:ready', function () {
            // Activate by config
            $.each(module.config['active'], function (id, url) {
                module.setActive(id, url);
            });
            // Reset active config.
            module.config['active'] = undefined;
        });
    };

    var setActive = function (id, item) {
        if (!id) {
            return;
        }

        if (!item) {
            return;
        }

        var $menu = $('#' + id);
        var $item = null;

        if (item.id) {
            $item = $menu.find('[data-menu-id="' + item.id + '"]');
        }

        if ((!$item || !$item.length) && item.url) {
            $item = $menu.find('[href="' + item.url + '"]');
        }
    };

    module.export({
        init: init,
        sortOrder: 100,
        setActive: setActive,
        initTopNav: initTopNav
    });
});
