humhub.module('humhub.ajaxLinkPager', function (module, require, $) {
    var client = require('client');

    var setPage = function(event) {
        setModalLoader(event);
        client.post(event).then(function(response) {
            $("#globalModal").html(response.data)
        });
    }

    module.export({
        setPage: setPage,
    });
});
