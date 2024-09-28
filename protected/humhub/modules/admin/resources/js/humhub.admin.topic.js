humhub.module('admin.topic', function (module, require, $) {
    var client = require('client');
    var loader = require('ui.loader');
    var status = require('ui.status');
    var event = require('event');



    var removeTopic = function (evt) {
        var $controls = evt.$trigger.parent();
        loader.set($controls, {size: '10px', css: {padding: '0px'}});
        client.post(evt).then(function (response) {
            console.log(response.success, $controls.closest('tr'))
            if (response.success) {
                $controls.closest('tr').fadeOut('slow', function () {
                    $(this).remove();
                });
                status.success(response.message)
            }
        }).catch(function (err) {
            module.log.error(err, true);
        })
    };

    var init = function () {
        event.on('humhub:ready', function (evt) {

            $('#global-topics-settings-form').find('input[type="checkbox"]').on('change', function() {
                $('#global-topics-settings-form').trigger('submit');
            });

            $('#topic-name').on('input', function() {
                var form = $(this).closest('form');
                form.find('.field-topic-name').removeClass('has-error').find('.help-block').remove();
            });
        });
    };

    module.export({
        init: init,
        removeTopic: removeTopic,
    });
});
