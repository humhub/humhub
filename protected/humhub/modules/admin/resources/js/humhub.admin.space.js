humhub.module('admin.space', function (module, require, $) {
    var modal = require('ui.modal');

    var init = function () {
        event.on('humhub:ready', function () {
            $('#spacesettingsform-defaultvisibility').on('change', function () {
                if (this.value === 0) {
                    $('#spacesettingsform-defaultjoinpolicy, #spacesettingsform-defaultcontentvisibility').val('0').prop('disabled', true);
                } else {
                    $('#spacesettingsform-defaultjoinpolicy, #spacesettingsform-defaultcontentvisibility').val('0').prop('disabled', false);
                }
            });
        });
    };

    var restrictTopicCreation = function (event) {
        if (!event.$target.is(':checked')) {
            var options = {
                'header': module.text('confirm.header'),
                'body': module.text('confirm.body'),
                'confirmText': module.text('confirm.confirmText')
            };

            modal.confirm(options).then(function (confirmed) {
                if (!confirmed) {
                    event.$target.prop('checked', true);
                }
            })
        }
    };

    module.export({
        init: init,
        restrictTopicCreation: restrictTopicCreation,
    });
});
