humhub.module('ui.imageset', function (module, require, $) {
    $(document).on('click', '.ui-imageset-show-more', function (e) {
        var $hiddenItems = $('.ui-imageset-hidden-items'),
            $button = $('.ui-imageset-show-more');
        if ($hiddenItems.css('display') === 'none') {
            $('.ui-imageset-hidden-items').show(100, function () {
                $button.attr('data-original-title', 'Hide');
            });
        } else {
            $('.ui-imageset-hidden-items').hide(100, function () {
                $button.attr('data-original-title', 'Show more');
            });
        }
    })
});
