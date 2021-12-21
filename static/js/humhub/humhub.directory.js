humhub.module('directory', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');

    const applyFilters = function(evt) {
        $(evt.$trigger).closest('form').submit();
    }

    const selectTag = function (evt) {
        const filter = $(evt.$trigger).data('filter');
        const tag = $(evt.$trigger).data('tag');
        const input = $(evt.$trigger).closest('form').find('input[type=hidden][name=' + filter + ']');

        if (tag === '') {
            input.val('');
            applyFilters(evt);
            return;
        }

        let currentTags = input.val();
        currentTags = currentTags === '' ? [] : currentTags.split(',');
        let newTags = [];
        let tagIsActive = false;
        for (let i = 0; i < currentTags.length; i++) {
            if (currentTags[i] === tag) {
                tagIsActive = true;
            } else {
                newTags.push(currentTags[i]);
            }
        }
        if (!tagIsActive) {
            newTags.push(tag);
        }

        input.val(newTags.join(','));
        applyFilters(evt);
    }

    const loadMore = function(directoryEndIndicator) {
        const urlParams = {page: directoryEndIndicator.data('current-page') + 1};

        $('.directory-end').data('isLoading', true);
        loader.append(directoryEndIndicator);
        client.get(module.config.loadMoreUrl, {data: urlParams}).then(function (response) {
            $('.container-directory .card:hidden').show();
            $('.container-directory .cards').append(response.response);
            if (urlParams.page == directoryEndIndicator.data('total-pages')) {
                // Remove the directory end indicator because the last page was loaded
                directoryEndIndicator.remove();
            } else {
                directoryEndIndicator.data('current-page', urlParams.page);
                hideLastNotCompletedRow();
            }
        }).catch(function(err) {
            module.log.error(err, true);
            reject();
        }).finally(function() {
            loader.reset(directoryEndIndicator);
            $('.directory-end').data('isLoading', false);
        });
    }

    const hideLastNotCompletedRow = function() {
        const cardsNum = $('.container-directory .card').length;
        if (!cardsNum) {
            return;
        }

        const directoryEndIndicator = $('.directory-end');
        if (directoryEndIndicator.data('current-page') === directoryEndIndicator.data('total-pages')) {
            // No reason to hide a not completed row if current page is last
            return;
        }

        const cardsPerRow = Math.floor($('.container-directory .row').outerWidth() / $('.container-directory .card:first').width());
        const hideLastCardsNum = cardsNum % cardsPerRow;
        if (hideLastCardsNum > 0 && cardsNum > cardsPerRow) {
            // Hide cards from not completed row
            $('.container-directory .card').slice(-hideLastCardsNum).hide();
        }
    }


    const preventScrollLoading = function () {
        return $('.directory-end').data('isLoading');
    };

    const initScroll = function () {
        if (!window.IntersectionObserver) {
            return;
        }

        const $directoryEndIndicator = $('.directory-end');
        if (!$directoryEndIndicator.length) {
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            if (preventScrollLoading()) {
                return;
            }

            if (entries.length && entries[0].isIntersecting) {
                loadMore($directoryEndIndicator);
            }
        }, {rootMargin: '1px'});

        observer.observe($directoryEndIndicator[0]);
    }

    const init = function() {
        hideLastNotCompletedRow();
        $('input.form-search-filter[name=keyword]').focus();
        initScroll();
    }

    module.export({
        initOnPjaxLoad: true,
        init,
        applyFilters,
        selectTag,
    });
});
