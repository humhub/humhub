humhub.module('admin.modules', function (module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');
    const status = require('ui.status');

    const update = function (evt) {
        startUpdate(evt);

        client.post(evt).then(function (response) {
            endSuccessUpdate(evt, response);
        }).catch(function (err) {
            endFailedUpdate(evt, err);
        });
    };

    const startUpdate = function(evt) {
        const card = evt.$trigger.closest('.card');
        card.css({width: card.outerWidth(), height: card.outerHeight()});
        evt.$trigger.parent().hide();
        loader.set(evt.$trigger.parent().prev(), {size: '12px'});
    }

    const endSuccessUpdate = function (evt, response) {
        const card = evt.$trigger.closest('.card');
        const body = card.find('.card-body');

        body.html('<div class="text-center"><span class="fa fa-check"></span></div>');
        const resultIcon = body.find('.fa').css({fontSize: 0, opacity: 0});
        resultIcon.animate({fontSize: '50px', opacity: 1}, 1000, function () {
            $(this).after('<div style="padding-top:20px">' + response.status + '</div>');
            setTimeout(function () {
                card.css({
                    position: 'absolute',
                    top: card.position().top,
                    left: card.position().left,
                });

                card.after('<div class="' + card.attr('class') + '"><div class="card-panel"></div></div>');
                card.next()
                    .css({opacity: 0, minHeight: card.outerHeight()})
                    .animate({width: 0}, 'slow', function () {$(this).remove()});
                card.animate({opacity: 0}, 'slow', function () {$(this).hide()});

                module.log.success('success.saved', true);
                status.success(response.message);
            }, 1000);
        });
    }

    const endFailedUpdate = function(evt, response) {
        module.log.error(response);
        status.error(response.message);

        evt.$trigger.parent().show();
        loader.reset(evt.$trigger.parent().prev());
    }

    module.export({
        update
    });
});