humhub.initModule('ui.navigation', function (module, require, $) {

    var event = require('event');

    module.initOnPjaxLoad = false;

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
        }).on('humhub:modules:space:changed', function () {
            $('#top-menu-nav').find('li').removeClass('active');
        });
    }

    var setActive = function (id, url) {
        module.setActiveItem($('#' + id).find('[href="' + url + '"]'));
    };

    var setActiveItem = function ($item) {
        if (!$item.length) {
            module.log.warn('Could not activate navigation item', $item);
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