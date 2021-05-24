humhub.module('people', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');

    const applyFilters = function(evt) {
        $(evt.$trigger).closest('form').submit();
    }

    const loadMore = function(evt) {
        const urlParams = {page: $(evt.$trigger).data('current-page') + 1};

        client.get(module.config.loadMoreUrl, {data: urlParams}).then(function (response) {
            $(evt.$trigger).parent().prev().before(response.response);
            if (urlParams.page == $(evt.$trigger).data('total-pages')) {
                $(evt.$trigger).parent().remove();
            } else {
                $(evt.$trigger).data('current-page', urlParams.page);
            }
        }).catch(function(err) {
            module.log.error(err, true);
            reject();
        }).finally(function() {
            loader.reset(evt.$trigger);
        });
    }

    module.export({
        applyFilters,
        loadMore,
    });
});
