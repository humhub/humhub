/**
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.gallery', function (module, require, $) {

    var init = function () {
        $(document).on('click.humhub:ui:gallery', '[data-ui-gallery]', function (evt) {
            evt.preventDefault();
            evt.stopPropagation();
            var $this = $(this);
            var gallery = $this.data('ui-gallery');
            var $links = (gallery) ? $('[data-ui-gallery="' + gallery + '"]') : $this.parent().find('[data-ui-gallery]');
            var options = {index: $this[0], event: evt.originalEvent};
            blueimp.Gallery($links.get(), options);
        });
    };

    module.export({
        init: init
    });
});