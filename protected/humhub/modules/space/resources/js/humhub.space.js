/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.module('space', function (module, require, $) {
    var client = require('client');
    var additions = require('ui.additions');
    var event = require('event');
    var i18n = require('i18n');

    // Current space options (guid, image)
    var options;

    module.requiredI18nCategories = ['base'];

    var isSpacePage = function () {
        return $('.space-layout-container').length > 0;
    };

    var setSpace = function (spaceOptions, pjax) {
        if (!module.options || module.options.guid !== spaceOptions.guid) {
            module.options = spaceOptions;
            if (pjax) {
                event.trigger('humhub:space:changed', $.extend({}, module.options));
            }
        }
    };

    var guid = function () {
        return (options) ? options.guid : null;
    };

    var archive = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.unarchive'));
                module.log.success(i18n.t('base', 'The space has been archived.'));
                event.trigger('humhub:space:archived', response.space);
            }
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    var unarchive = function (evt) {
        client.post(evt).then(function (response) {
            if (response.success) {
                additions.switchButtons(evt.$trigger, evt.$trigger.siblings('.archive'));
                module.log.success(i18n.t('base', 'The space has been unarchived.'));

                event.trigger('humhub:space:unarchived', response.space);
            }
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    /**
     * `data-action-click="space.leave"`: ends the current user's membership through the same
     * endpoint the membership button uses - `data-action-url` is `DELETE /api/v2/space/<id>/membership`
     * (see `space\widgets\HeaderControlsMenu`). The confirmation is the action framework's
     * (`data-action-confirm`). Afterwards the page is reloaded, since header, menu and the
     * content the user may still see all depend on the membership.
     */
    var leave = function (evt) {
        client.ajax(evt.url, {type: 'DELETE', method: 'DELETE'}).then(function () {
            client.reload(true);
        }).catch(function (err) {
            module.log.error(err, true);
            evt.finish();
        });
    };

    var changeVisibilityOption = function (event) {
        const form = event.$trigger.closest('form');
        const submitButton = form.find(':submit');
        const fields = form.find('select[name*=join_policy], select[name*=default_content_visibility]');

        if (event.$trigger.val() === '0') {
            // Private visibility
            submitButton.attr('data-action-confirm', submitButton.data('confirm-text'));
            fields.val(0).prop('disabled', true);
        } else {
            // Public or guest visibility
            submitButton.removeAttr('data-action-confirm');
            fields.prop('disabled', false);
        }
    };

    var init = function () {
        if (!module.isSpacePage()) {
            module.options = undefined;
        }
    };

    module.export({
        init: init,
        initOnPjaxLoad: true,
        guid: guid,
        archive: archive,
        unarchive: unarchive,
        leave: leave,
        isSpacePage: isSpacePage,
        setSpace: setSpace,
        changeVisibilityOption: changeVisibilityOption,
    });
});
