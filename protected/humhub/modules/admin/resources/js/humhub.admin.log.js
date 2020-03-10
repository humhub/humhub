humhub.module('admin.log', function(module, require, $) {
    var Widget = require('ui.widget').Widget;
    var LogFilterForm = Widget.extend();
    var client = require('client');
    var loader = require('ui.loader');

    var SELECTOR_ENTRIES = '#admin-log-entries';

    LogFilterForm.prototype.init = function() {
        this.$form = this.$.find('form');
        var that = this;
        this.$.find('input, select').on('change', function() {
            that.options.widgetLoader = $(SELECTOR_ENTRIES);
            that.loader();

            var options = {beforeSend : function (xhr) {
                that.reloadXhr = xhr;
            }};

            if(that.currentRequest) {
                that.currentRequest.abort();
            }

            that.currentRequest = client.submit(that.$form);
            that.currentRequest.then(function(response) {
                that.currentRequest = null;
                $(SELECTOR_ENTRIES).fadeOut('fast',function() {
                    $(this).replaceWith(response.html);
                    history.replaceState(null, null, response.url);
                });
            });
        });
    };

    module.export({
        LogFilterForm: LogFilterForm
    });
});
