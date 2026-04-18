humhub.module('admin.space', function (module, require, $) {
    var modal = require('ui.modal');
    var i18n = require('i18n');

    module.requiredI18nCategories = ['AdminModule.user'];

    var restrictTopicCreation = function (event) {
        if (!event.$target.is(':checked')) {
            var options = {
                'header': i18n.t('AdminModule.user', 'Convert Profile Topics'),
                'body': i18n.t('AdminModule.user', 'All existing Profile Topics will be converted to Global Topics.'),
                'confirmText': i18n.t('AdminModule.user', 'Convert')
            };

            modal.confirm(options).then(function (confirmed) {
                if (!confirmed) {
                    event.$target.prop('checked', true);
                }
            })
        }
    };

    module.export({
        restrictTopicCreation: restrictTopicCreation,
    });
});
