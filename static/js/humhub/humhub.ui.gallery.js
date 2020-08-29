/**
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.gallery', function (module, require, $) {

    var init = function () {
        var firstImageIsLoaded = false;
        $(document).on('click.humhub:ui:gallery', '[data-ui-gallery]', function (evt) {
            var $this = $(this);

            if ($this.is('img') && $this.closest('a').length) {
                return;
            }

            evt.preventDefault();
            evt.stopPropagation();

            // var initFirstView = function () {
            //     var $slides = $('#blueimp-gallery .slides');
            //     var firstAnimationTimeInMs = 1550;
            //     $slides.css({'opacity': 0.1});
            //     $slides.fadeTo(firstAnimationTimeInMs, 1);
            // }
            // var $slides = $('#blueimp-gallery .slides');
            // console.log('$this.is(\'img\')', $this.is('img'));
            // console.log('$slides.find(\'.slide img\')', $slides.find('.slide img'));
            //
            // if($('.slides').children().length <= 0){
            //     initFirstView();
            // }

            var gallery = $this.data('ui-gallery');
            var $links = (gallery) ? $('[data-ui-gallery="' + gallery + '"]') : $this.parent().find('[data-ui-gallery]');
            var options = {index: $this[0], event: evt.originalEvent};

            if ($this.is('img')) {
                options['urlProperty'] = 'src';
            }
            blueimp.Gallery($links.get(), options);

            var $slides = $('#blueimp-gallery .slides');

            var animatePreviewForFirstTime = function () {
                $slides.css({'opacity': 0.1});
                var $firstImage = $slides.find('.slide img') ? $slides.find('.slide img')[0] : '';

                if ($firstImage !== '' && $firstImage.complete) {
                    var firstAnimationTimeInMs = 1250;

                    $slides.fadeTo(firstAnimationTimeInMs, 1);
                    firstImageIsLoaded = true;
                } else {
                    $slides.css({'opacity': 1});
                }
            }
            if (!firstImageIsLoaded) {
                animatePreviewForFirstTime();
            }

        });
    };

    module.export({
        init: init,
        sortOrder: 100,
    });
});
