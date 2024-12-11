humhub.module('admin.topic', function (module, require, $) {
    var client = require('client');
    var loader = require('ui.loader');
    var status = require('ui.status');
    var event = require('event');

    var convertTopic = function (evt) {
        $('input[name="convert-to-global"]').val(1);
        evt.$trigger.closest('form').trigger('submit');
    };

    var removeTopic = function (evt) {
        var $controls = evt.$trigger.parent();
        loader.set($controls, {size: '10px', css: {padding: '0px'}});
        client.post(evt).then(function (response) {
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
            $('#addtopicform-name, #addtopicform-converttoglobal').on('input', function() {
                var form = $(this).closest('form');
                form.find('.field-addtopicform-name').removeClass('has-error').find('.help-block').remove();
            });
        });
    };

    module.export({
        init: init,
        convertTopic: convertTopic,
        removeTopic: removeTopic,
    });
});
