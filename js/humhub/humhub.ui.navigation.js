/**
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.initModule('ui.navigation', function (module, require, $) {

    var init = function () {
        // Default implementation for topbar. Activate li on click.
       $('#top-menu-nav a').on('click', function () {
            var $this = $(this);
            if (!$this.is('#space-menu')) {
                setActiveItem($this);
            }
        });

        // Activate by config
        $.each(module.config['active'], function (id, url) {
            setActive(id, url);
        });
        
        // Reset active config.
        module.config['active'] = undefined;
    };
    
    var setActive = function (id, url) {
        setActiveItem($('#' + id).find('[href="' + url + '"]'));
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
        setActive: setActive
    });
});