humhub.module('ui.navigation', function (module, require, $) {

    var event = require('event');

    var init = function () {
        module.initTopNav();
    };

    var initTopNav = function () {
        // Default implementation for topbar. Activate li on click.
        $('#top-menu-nav a').on('click', function () {
            var $this = $(this);
            if (!$this.is('#space-menu')) {
                module.setActiveItem($this);
            }
        });

        event.on('humhub:ready', function () {
            // Activate by config
            $.each(module.config['active'], function (id, url) {
                module.setActive(id, url);
            });
            // Reset active config.
            module.config['active'] = undefined;
        }).on('humhub:space:changed', function () {
            $('#top-menu-nav').find('li').removeClass('active');
        });
    };

    var setActive = function (id, item) {
        if(!id) {
            return;
        }
        
        if(item && item.url) {
            module.setActiveItem($('#' + id).find('[href="' + item.url + '"]'));
        } else {
            module.setActiveItem(null);
        }
    };

    var setActiveItem = function ($item) {
        if (!$item || !$item.length) {
            $('#top-menu-nav li').removeClass('active');
            return;
        }
        
        $item.closest('ul').find('li').removeClass('active');
        $item.closest('li').addClass('active');
        $item.trigger('blur');
    };

    module.export({
        init: init,
        setActive: setActive,
        initTopNav: initTopNav,
        setActiveItem: setActiveItem
    });
});