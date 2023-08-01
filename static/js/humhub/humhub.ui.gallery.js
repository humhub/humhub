/**
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.gallery', function (module, require, $) {
    var lastTap = 0;
    var timeout;

    var init = function () {
        $(document).on('click.humhub:ui:gallery', '[data-ui-gallery]', function (evt) {
            var $this = $(this);

            if ($this.is('img') && $this.closest('a').length) {
                return;
            }

            evt.preventDefault();
            evt.stopPropagation();

            var gallery = $this.data('ui-gallery');
            var $links = (gallery) ? $('[data-ui-gallery="' + gallery + '"]') : $this.parent().find('[data-ui-gallery]');
            var options = {index: $this[0], event: evt.originalEvent};

            if ($this.is('img')) {
                options['urlProperty'] = 'src';
            }
            blueimp.Gallery($links.get(), options);
        });

        // Zooming in on small images by double-tap or double-click
        $('#blueimp-gallery').on('touchend click', '.slides .slide > img', function (evt) {
            var currentTime = new Date().getTime();
            var tapLength = currentTime - lastTap;

            evt.preventDefault();
            clearTimeout(timeout);

            if (tapLength < 500 && tapLength > 0) {
                $(evt.target).toggleClass('contain-img');
            } else {
                timeout = setTimeout(function () {
                    clearTimeout(timeout);
                }, 500);
            }

            lastTap = currentTime;
        });
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
