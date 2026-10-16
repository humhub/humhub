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
    var highlightWords = require('ui.additions').highlightWords;

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

        that.actionForm = form;
        that.highlightInput = form.find('input[data-highlight]');

        that.actionForm.setContent = function (content) {
            const resultsContent = that.getActionContent().find('[data-stream-action-form-results]');
            if (resultsContent.length) {
                resultsContent.append(content);
            } else {
                that.getActionContent().html(content);
            }
            const widgets = that.getActionContent().find(that.highlightInput.data('highlight') + ' [data-ui-widget]');

            if (that.highlightInput.length && that.highlightInput.val() !== '') {
                widgets.on('afterInit', function() {
                    if (!$(this).data('isHighlighted')) {
                        $(this).data('isHighlighted', true);
                        highlightWords(this, that.highlightInput.val());
                    }
                });
            }

            widgets.each(function () {
                Widget.instance($(this));
            });
        }

        const filters = function () {
            return form.serialize().replace(/(^|&)r=.*?(&|$)/, '$1');
        }

        const setStreamUrl = function () {
            // Set stream URL from the form action URL and current filters
            that.options.stream = form.data('action-url')
                + (form.data('action-url').indexOf('?') === -1 ? '?' : '&')
                + filters();
        }

        form.on('submit', function (e) {
            e.preventDefault();
            const params = filters();
            const content = that.getActionContent();
            loader.set(content);
            that.refreshAddressBar(params);
            setStreamUrl();
            // Lock loading of data on scroll down indicator
            that.state.scrollLock = true;

            client.get(form.data('action-url'), {data: params}).then(function (response) {
                that.handleResponseActionForm(response);
            }).catch(function (err) {
                module.log.error(err, true);
            }).finally(function () {
                that.state.scrollLock = false;
            });
        });

        setStreamUrl();

        // Activate auto load next pages by scroll down action
        that.options.scrollSupport = true;
        that.options.scrollOptions = {root: null, rootMargin: '100px'};
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

        const data = {
            id: entry.getKey(),
            viewContext: entry.data('view-context'),
        };
        entry.loader();
        return client.get(content.config.reloadUrl, {data}).then(function (response) {
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

    SimpleStream.prototype.handleResponse = function (request) {
        return typeof this.actionForm !== 'undefined'
            ? this.handleResponseActionForm(request.response)
            : Stream.prototype.handleResponse.call(this, request);
    }

    SimpleStream.prototype.handleResponseActionForm = function (response) {
        this.actionForm.setContent(response.content);
        this.state.lastEntryLoaded = response.isLast;

        // Update stream URL for next page
        this.options.stream = this.options.stream.replace(/(\?|&)page=\d+/i, '$1')
            + '&page=' + (parseInt(response.page) + 1);
        this.options.stream = this.options.stream.replace('?&', '?').replace('&&', '&');

        // Load next page when the stream end indicator is visible on screen
        const streamEnd = this.$content.find('.stream-end:first');
        if (streamEnd.length && !response.isLast &&
            streamEnd.offset().top - 100 < $(window).scrollTop() + $(window).innerHeight()) {
            this.load();
        }

        return Promise.resolve();
    }

    module.export = SimpleStream;
});
