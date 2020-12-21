
humhub.module('space.settings', function(module, require, $) {
    var modal = require('ui.modal');

    var confirmVisibilityChange = function (evt) {
        if (evt.$trigger.val() === '0') {
            modal.confirm().then(function(confirmed) {
                if(confirmed) {
                    $('#space-join_policy, #space-default_content_visibility').val('0').prop('disabled', true);
                } else {
                    evt.$trigger.prop('selectedIndex', 1);
                }
            });
        } else {
            $('#space-join_policy, #space-default_content_visibility').val('0').prop('disabled', false);
        }
    };

    module.export({
        confirmVisibilityChange: confirmVisibilityChange
    });
});
