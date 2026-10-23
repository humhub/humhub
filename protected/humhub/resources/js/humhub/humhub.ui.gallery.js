/**
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.gallery', function (module, require, $) {

    /**
     * Currently opened gallery instance (if any), used to close the overlay on browser back navigation.
     */
    var current = null;

    var init = function () {
        // Close an open gallery overlay when the user navigates with the browser back/forward buttons
        $(window).off('popstate.humhub:ui:gallery').on('popstate.humhub:ui:gallery', function () {
            if (current) {
                current.close();
                current = null;
            }
        });

        $(document).on('click.humhub:ui:gallery', '[data-ui-gallery]', function (evt) {
            var $this = $(this);

            if($this.is('img') && $this.closest('a').length) {
                return;
            }

            evt.preventDefault();
            evt.stopPropagation();

            var gallery = $this.data('ui-gallery');
            var $links = (gallery) ? $('[data-ui-gallery="' + gallery + '"]') : $this.parent().find('[data-ui-gallery]');
            var options = {index: $this[0], event: evt.originalEvent};

            if($this.is('img')) {
                options['urlProperty'] = 'src';
            }
            options['onclosed'] = function () {
                current = null;
            };

            current = blueimp.Gallery($links.get(), options);
        });
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
