humhub.module('admin.group', function (module, require, $) {
    var client = require('client');
    var loader = require('ui.loader');

    var setManagerRole = function (evt) {
        var options = {
            data: {
                'id': evt.$trigger.data('groupid'),
                'userId': evt.$trigger.data('userid'),
                'value': evt.$trigger.val()
            }
        };

        var $controls = evt.$trigger.parent().next('td');
        loader.set($controls, {size: '10px', css: {padding: '0px'}});

        client.post(evt, options).then(function (response) {
            if (response.success) {
                module.log.success('success.saved', true);
            } else {
                module.log.error(response);
            }
        }).catch(function (err) {
            module.log.error(err);
        }).finally(function () {
            loader.reset($controls);
        });
    };

    var removeMember = function (evt) {
        var $controls = evt.$trigger.parent();
        loader.set($controls, {size: '10px', css: {padding: '0px'}});
        client.post(evt).then(function (response) {
            if (response.success) {
                module.log.success('success.saved');
                $controls.closest('tr').fadeOut('slow', function() {
                    $(this).remove();
                });
            } else {
                module.log.error(response, true);
            }
        }).catch(function (err) {
            module.log.error(err, true);
        }).finally(function () {
            loader.reset($controls);
        });
    };

    module.export({
        setManagerRole: setManagerRole,
        removeMember: removeMember
    });
});