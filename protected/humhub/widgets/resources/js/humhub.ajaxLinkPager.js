humhub.module('humhub.ajaxLinkPager', function (module, require, $) {
    var client = require('client');
    var modal = require('ui.modal');

    var setPage = function(event) {
        modal.footerLoader();
        client.post(event).then(function(response) {
            modal.setContent(response.data);
        });
    }

    module.export({
        setPage: setPage,
    });
});
