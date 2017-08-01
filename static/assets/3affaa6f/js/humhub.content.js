/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.module('content', function (module, require, $) {
    var client = require('client');
    var util = require('util');
    var object = util.object;
    var string = util.string;
    var actions = require('action');
    var Component = actions.Component;
    var event = require('event');
    var modal = require('ui.modal');

    var DATA_CONTENT_KEY = "content-key";
    var DATA_CONTENT_EDIT_URL = "content-edit-url";
    var DATA_CONTENT_SAVE_SELECTOR = "[data-content-save]";
    var DATA_CONTENT_DELETE_URL = "content-delete-url";


    Component.addSelector('content-component');

    var Content = function (container) {
        Component.call(this, container);
    };

    object.inherits(Content, Component);

    Content.getNodeByKey = function (key) {
        return $('[data-content-key="' + key + '"]');
    };

    Content.prototype.actions = function () {
        return ['create', 'edit', 'delete'];
    };

    Content.prototype.getKey = function () {
        return this.$.data(DATA_CONTENT_KEY);
    };

    Content.prototype.create = function (addContentHandler) {
        //Note that this Content won't have an id, so the backend will create an instance
        if (this.hasAction('create')) {
            return;
        }

        this.edit(addContentHandler);
    };

    Content.prototype.edit = function (successHandler) {
        // Currently there is no need for a default implementation
    };

    Content.prototype.delete = function (options) {
        options = options || {};
        var that = this;
        return new Promise(function (resolve, reject) {
            if (!that.hasAction('delete')) {
                return;
            }

            var modalOptions = options.modal || module.config.modal.deleteConfirm;

            modal.confirm(modalOptions).then(function ($confirmed) {
                if (!$confirmed) {
                    resolve(false);
                    return;
                }

                that.loader();
                var deleteUrl = that.data(DATA_CONTENT_DELETE_URL) || module.config.deleteUrl;
                if (deleteUrl) {
                    client.post(deleteUrl, {
                        data: {id: that.getKey()}
                    }).then(function (response) {
                        that.remove().then(function () {
                            resolve(true);
                        });
                    }).catch(function (err) {
                        reject(err);
                    }).finally(function () {
                        that.loader(false);
                    });
                } else {
                    reject('Content delete was called, but no url could be determined for ' + that.base);
                    that.loader(false);
                }
            });
        });
    };

    /**
     * Abstract loader function which can be used to activate or deactivate a
     * loader within a content entry.
     * 
     * If $show is undefined or true the loader animation should be rendered
     * otherwise it should be remved.
     * 
     * @param {type} $show
     * @returns {undefined}
     */
    Content.prototype.loader = function ($show) {
        // Has to be overwritten by content type
    };

    Content.prototype.remove = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            that.$.animate({height: 'toggle', opacity: 'toggle'}, 'fast', function () {
                that.$.remove();
                event.trigger('humhub:modules:content:afterRemove', that);
                resolve(that);
            });
        });
    };

    Content.prototype.permalink = function (evt) {
        var options = module.config.modal.permalink;
        options.permalink = evt.$trigger.data('content-permalink');

        modal.global.set({
            header: options.head,
            body: string.template(module.templates.permalinkBody, options),
            footer: string.template(module.templates.permalinkFooter, options),
            size: 'normal'
        }).show();

        modal.global.$.find('textarea').focus().select();

        // Make sure the modal is closed when pjax loads
        event.one('humhub:ready', function () {
            modal.global.close();
        });
    };

    var templates = {
        permalinkBody: '<div class="clearfix"><textarea rows="3" class="form-control permalink-txt" spellcheck="false" readonly>{permalink}</textarea><p class="help-block pull-right"><a href="#" data-action-click="copyToClipboard" data-action-target=".permalink-txt"><i class="fa fa-clipboard" aria-hidden="true"></i> {info}</a></p></div>',
        permalinkFooter: '<a href="#" data-modal-close class="btn btn-default">{buttonClose}</a><a href="{permalink}" class="btn btn-primary" data-ui-loader>{buttonOpen}</a>'
    };

    module.export({
        Content: Content,
        templates: templates
    });
});