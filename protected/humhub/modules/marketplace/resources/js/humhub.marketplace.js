humhub.module('marketplace', function (module, require, $) {
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
        evt.$trigger.removeAttr('data-update-status');
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
                card.animate({opacity: 0}, 'slow', function () {
                    $(this).remove();
                    const availableUpdates = $('[data-action-click="marketplace.update"]').length;
                    $('.group-modules-count-availableUpdates').html(availableUpdates);
                    if (availableUpdates === 0) {
                        $('[data-action-click="marketplace.updateAll"]').remove();
                        $('.container-module-updates').animate({opacity: 0, height: 0, padding: 0, margin: 0}, 2000);
                    }
                });

                module.log.success('success.saved', true);
                status.success(response.message);
                evt.$trigger.attr('data-update-status', 'success');

                runNextUpdate();
            }, 1000);
        });
    }

    const endFailedUpdate = function(evt, response) {
        module.log.error(response);
        status.error(response.message);
        evt.$trigger.attr('data-update-status', 'failed')
            .attr('title', response.message);

        evt.$trigger.parent().show();
        loader.reset(evt.$trigger.parent().prev());

        runNextUpdate();
    }

    const updateAll = function (evt) {
        const btn = evt.$trigger;

        if (btn.data('is-updating-all')) {
            stopUpdateAll();
            return;
        }

        btn.data('is-updating-all', true)
            .data('orig-title', btn.html())
            .data('orig-class', btn.attr('class'))
            .html(btn.data('stop-title'))
            .attr('class', btn.data('stop-class'));

        $('[data-action-click="marketplace.update"]').removeAttr('data-update-status');

        runNextUpdate();
    }

    const runNextUpdate = function() {
        const updateAllButton = $('[data-action-click="marketplace.updateAll"]');
        if (!updateAllButton.data('is-updating-all')) {
            return;
        }

        const nextButton = $('[data-action-click="marketplace.update"]:not([data-update-status=failed]):first');
        if (nextButton.length) {
            nextButton.click();
        } else {
            stopUpdateAll();
        }
    }

    const stopUpdateAll = function () {
        const updateAllButton = $('[data-action-click="marketplace.updateAll"]');
        updateAllButton.data('is-updating-all', false)
            .html(updateAllButton.data('orig-title'))
            .attr('class', updateAllButton.data('orig-class'));
    }

    const registerLicenceKey = function(evt) {
        const form = evt.$trigger.closest('form');
        const licenceKey = form.find('input[name=licenceKey]').val();

        loader.set(form);

        client.post(form.attr('action'), {data: {licenceKey}}).then(function (response) {
            form.closest('.modal-dialog').after(response.html).remove();
        }).catch(function (err) {
            module.log.error(err);
            status.error(err.message);
            loader.reset(form);
        });
    }

    module.export({
        update,
        updateAll,
        registerLicenceKey,
    });
});