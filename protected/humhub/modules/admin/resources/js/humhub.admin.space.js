humhub.module('admin.space', function (module, require, $) {
    var modal = require('ui.modal');
    var i18n = require('i18n');

    module.requiredI18nCategories = ['AdminModule.space'];

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
                'header': i18n.t('AdminModule.space', 'Convert Space Topics'),
                'body': i18n.t('AdminModule.space', 'All existing Space Topics will be converted to Global Topics.'),
                'confirmText': i18n.t('AdminModule.space', 'Convert')
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
