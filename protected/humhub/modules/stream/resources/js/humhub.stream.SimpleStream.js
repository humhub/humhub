/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Simple stream is used for
 * @type Function
 */
humhub.module('stream.SimpleStream', function (module, require, $) {
    var Stream = require('stream').Stream;
    var Component = require('action').Component;
    var client = require('client');
    var loader = require('ui.loader');
    var content = require('content');
    var Url = require('ui.filter').Url;
    var Widget = require('ui.widget').Widget;

    /**
     * Simple stream component can be used for static streams without load logic (only reload single content).
     *
     * @param {type} container
     * @param {type} cfg
     */
    var SimpleStream = Stream.extend(function (container, cfg) {
        Stream.call(this, container, cfg);
        this.$content = this.$;
        this.setFilter('entry_archived');
    });

    SimpleStream.prototype.init = function () {
        this.initActionForm();
        Stream.prototype.init.call(this);
    };

    SimpleStream.prototype.initActionForm = function () {
        const that = this;
        const form = this.getActionForm();
        if (!form.length) {
            return;
        }

        const submit = function (form) {
            const params = form.serialize().replace(/(^|&)r=.*?(&|$)/, '$1');
            const content = that.getActionContent();
            loader.set(content);
            that.refreshAddressBar(params);

            client.get(form.data('action-url'), {data: params}).then(function (response) {
                content.html(response.response).find('[data-ui-widget]').each(function () {
                    Widget.instance($(this));
                });
            }).catch(function (err) {
                module.log.error(err, true);
            });
        }

        form.on('submit', function (e) {
            e.preventDefault();
            submit($(this));
        }).first().submit();

        // Prevent auto loading of stream content when the action form is used instead
        that.options.autoLoad = false;
    };

    SimpleStream.prototype.onEmptyStream = function () {
        var modal = Component.instance(this.$.closest('.modal'));

        if (modal) {
            modal.close();
        }
    };

    SimpleStream.prototype.reloadEntry = function (entry) {
        if (!entry) {
            entry = Component.instance(this.$.find('[data-stream-entry]:first'));
        }

        entry.loader();
        var contentId = entry.getKey();
        return client.get(content.config.reloadUrl, {data: {id: contentId}}).then(function (response) {
            if (response.output) {
                entry.replace(response.output);
            }
            return response;
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    SimpleStream.prototype.loadEntry = function (contentId) {
        var that = this;

        return client.get(content.config.reloadUrl, {data: {id: contentId}}).then(function (response) {
            that.appendEntry(response.output);
            return response;
        });
    };

    SimpleStream.prototype.getActionForm = function () {
        return this.$.find('form[data-action-url]');
    }

    SimpleStream.prototype.getActionContent = function () {
        return this.$.find(this.options.contentSelector);
    }

    SimpleStream.prototype.switchPage = function (e) {
        const content = this.getActionContent();
        if (!content) {
            return;
        }

        e.preventDefault();
        loader.set(content);
        this.refreshAddressBar(e.url);

        client.get(e).then(function (response) {
            content.html(response.response).find('[data-ui-widget]').each(function () {
                Widget.instance($(this));
            });
        }).catch(function (err) {
            module.log.error(err, true);
        });
    };

    SimpleStream.prototype.refreshAddressBar = function (params) {
        var newParams = [];

        // Update address bar with new params
        params.substring(params.indexOf('?') + 1)
            .split('&')
            .forEach((param) => {
                param = param.split('=');
                if (param[0] !== 'r') {
                    if (typeof param[1] === 'undefined' || param[1] === '') {
                        Url.removeParam(param[0])
                    } else {
                        Url.updateParam(param[0], param[1])
                        newParams.push(param[0]);
                    }
                }
            });

        // Remove params from previous request
        const currentUrl = Url.url();
        currentUrl.substring(currentUrl.indexOf('?') + 1)
            .split('&')
            .forEach((param) => {
                param = param.split('=');
                if (param[0] !== 'r' && newParams.indexOf(param[0]) === -1) {
                    Url.removeParam(param[0])
                }
            });
    };

    module.export = SimpleStream;
});
