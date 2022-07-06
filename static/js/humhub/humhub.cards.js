humhub.module('cards', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');

    const applyFilters = function(evt) {
        const form = $(evt.$trigger).closest('form');
        form.find('select[data-select2-id] option[data-id]').each(function() {
            $(this).val($(this).data('id'));
        });
        form.submit();
    }

    const selectTag = function (evt) {
        const filter = $(evt.$trigger).data('filter');
        const tag = $(evt.$trigger).data('tag');
        const isMultiple = $(evt.$trigger).data('multiple');
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
            } else if (isMultiple) {
                newTags.push(currentTags[i]);
            }
        }
        if (!tagIsActive) {
            newTags.push(tag);
        }

        input.val(newTags.join(','));
        applyFilters(evt);
    }

    const loadMore = function(cardsEndIndicator) {
        const urlParams = {page: cardsEndIndicator.data('current-page') + 1};

        $('.cards-end').data('isLoading', true);
        loader.append(cardsEndIndicator);
        client.get(module.config.loadMoreUrl, {data: urlParams}).then(function (response) {
            $('.container-cards .card:hidden').show();
            $('.container-cards .cards').append(response.response);
            if (urlParams.page == cardsEndIndicator.data('total-pages')) {
                // Remove the directory end indicator because the last page was loaded
                cardsEndIndicator.remove();
            } else {
                cardsEndIndicator.data('current-page', urlParams.page);
                hideLastNotCompletedRow();
            }
        }).catch(function(err) {
            module.log.error(err, true);
            reject();
        }).finally(function() {
            loader.reset(cardsEndIndicator);
            $('.cards-end').data('isLoading', false);
        });
    }

    const hideLastNotCompletedRow = function() {
        const cardsNum = $('.container-cards .card').length;
        if (!cardsNum) {
            return;
        }

        const cardsEndIndicator = $('.cards-end');
        if (cardsEndIndicator.data('current-page') === cardsEndIndicator.data('total-pages')) {
            // No reason to hide a not completed row if current page is last
            return;
        }

        const cardsPerRow = Math.floor($('.container-cards .row').outerWidth() / $('.container-cards .card:first').width());
        const hideLastCardsNum = cardsNum % cardsPerRow;
        if (hideLastCardsNum > 0 && cardsNum > cardsPerRow) {
            // Hide cards from not completed row
            $('.container-cards .card').slice(-hideLastCardsNum).hide();
        }
    }


    const preventScrollLoading = function () {
        return $('.cards-end').data('isLoading');
    };

    const initScroll = function () {
        if (!window.IntersectionObserver) {
            return;
        }

        const $cardsEndIndicator = $('.cards-end');
        if (!$cardsEndIndicator.length) {
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            if (preventScrollLoading()) {
                return;
            }

            if (entries.length && entries[0].isIntersecting) {
                loadMore($cardsEndIndicator);
            }
        }, {rootMargin: '1px'});

        observer.observe($cardsEndIndicator[0]);
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
