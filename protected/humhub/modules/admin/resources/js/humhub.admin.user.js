humhub.module('admin.space', function (module, require, $) {
    var modal = require('ui.modal');

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
        restrictTopicCreation: restrictTopicCreation,
    });
});
