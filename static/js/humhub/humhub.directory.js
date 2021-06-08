humhub.module('directory', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');

    const applyFilters = function(evt) {
        $(evt.$trigger).closest('form').submit();
    }

    const loadMore = function(evt) {
        const urlParams = {page: $(evt.$trigger).data('current-page') + 1};

        client.get(module.config.loadMoreUrl, {data: urlParams}).then(function (response) {
            $('.container-directory .card:hidden').show();
            $('.container-directory .cards').append(response.response);
            if (urlParams.page == $(evt.$trigger).data('total-pages')) {
                // Remove button "Load more" because the last page was loaded
                $(evt.$trigger).parent().remove();
            } else {
                $(evt.$trigger).data('current-page', urlParams.page);
                hideLastNotCompletedRow();
            }
        }).catch(function(err) {
            module.log.error(err, true);
            reject();
        }).finally(function() {
            loader.reset(evt.$trigger);
        });
    }

    const hideLastNotCompletedRow = function() {
        const cardsNum = $('.container-directory .card').length;
        if (!cardsNum) {
            return;
        }

        const loadMoreButton = $('.directory-load-more button');
        if (loadMoreButton.data('current-page') === loadMoreButton.data('total-pages')) {
            // No reason to hide a not completed row if current page is last
            return;
        }

        // Display button to load more cards
        loadMoreButton.parent().show();

        const cardsPerRow = Math.floor($('.container-directory .row').outerWidth() / $('.container-directory .card:first').width());
        const hideLastCardsNum = cardsNum % cardsPerRow;
        if (hideLastCardsNum > 0 && cardsNum > cardsPerRow) {
            // Hide cards from not completed row
            $('.container-directory .card').slice(-hideLastCardsNum).hide();
        }
    }

    const init = function() {
        hideLastNotCompletedRow();
        $('input.form-search-filter[name=keyword]').focus();
    }

    module.export({
        initOnPjaxLoad: true,
        init,
        applyFilters,
        loadMore,
    });
});
