/**
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.gallery', function (module, require, $) {

    var init = function () {

        var initImageAnimated = false;
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

            var initFirstView = function () {
                var $slides = $('#blueimp-gallery .slides');
                var firstAnimationTime = 2450;
                $slides.css({'opacity': 0.1});
                $slides.fadeTo(firstAnimationTime, 1);
                initImageAnimated = true;
            }
            if (!initImageAnimated) {
                initFirstView();
                $('.slide-loading').css({ backgroundImage: 'none'});
            }

        });
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
