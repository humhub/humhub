humhub.module('cards', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');

    const applyFilters = function(evt) {
        $(evt.$trigger).closest('form').submit();
    }

    const initFiltersForm = function () {
        const form = $('form.form-search');
        if (!('ontouchstart' in window || navigator.maxTouchPoints > 0)) {
            form.find('input[type=text]:first').focus();
        }
        form.on('submit', function () {
            $(this).find('select[data-select2-id] option[data-id]').each(function() {
                $(this).val($(this).data('id'));
            });
            if (form.data('action-url') !== undefined) {
                $('.container-cards .form-search-action-reset').show();
            }
        });
    }

    const initMoreFiltersVisibility = function() {
        const togglerSelector = '.container-cards .form-search-action-toggle-more';
        const filterSelector = '.container-cards .form-search .d-flex .flex-fill';
        var togglerIsVisibleCurrent = $(togglerSelector).is(':visible');
        var togglerIsVisiblePrevious = togglerIsVisibleCurrent;

        if (togglerIsVisibleCurrent) {
            const resetSelector = '.container-cards .form-search-action-reset';
            const initState = getMoreFiltersState() && $(resetSelector).length > 0;
            toggleMoreFilters(initState, false, 'none');
        }

        $(window).on('resize', function() {
            togglerIsVisibleCurrent = $(togglerSelector).is(':visible');

            if (!togglerIsVisibleCurrent && $(filterSelector + ':hidden').length) {
                toggleMoreFilters(true, false);
            }

            if (togglerIsVisiblePrevious !== togglerIsVisibleCurrent && togglerIsVisibleCurrent) {
                toggleMoreFilters(getMoreFiltersState());
            }

            togglerIsVisiblePrevious = $(togglerSelector).is(':visible');
        });
    }

    const toggleMoreFilters = function(evt, updateState = true, effect = 'slide') {
        const toggler = typeof(evt) === 'boolean' ? $('.form-search-action-toggle-more').find('.btn') : $(evt.$trigger);
        const show = typeof(evt) === 'boolean' ? evt : !toggler.hasClass('active');
        const moreFilters = toggler.closest('.d-flex').find('.form-search-action').last().nextAll('.flex-fill').stop();
        if (effect === 'slide') {
            show ? moreFilters.slideDown(200) : moreFilters.slideUp(200);
        } else {
            moreFilters.toggle(show);
        }
        toggler.toggleClass('active', show);
        if (updateState) {
            updateMoreFiltersState(show);
        }
    }

    const getMoreFiltersStates = function () {
        const states = window.localStorage.getItem('cards-more-filters');
        return states ? JSON.parse(states) : {};
    }

    const getMoreFiltersState = function (defaultState = false) {
        const states = getMoreFiltersStates();
        return typeof(states[getMoreFiltersPage()]) === 'undefined'
            ? defaultState
            : states[getMoreFiltersPage()];
    }

    const getMoreFiltersPage = function () {
        return $('.form-search-action-toggle-more').closest('form').attr('action').replace(/^\/*(.+)\/*$/, '$1');
    }

    const updateMoreFiltersState = function (state) {
        const states = getMoreFiltersStates();
        states[getMoreFiltersPage()] = state;
        window.localStorage.setItem('cards-more-filters', JSON.stringify(states));
    }

    const selectTag = function (evt) {
        const filter = $(evt.$trigger).data('filter');
        const tag = $(evt.$trigger).data('tag');
        const input = $(evt.$trigger).closest('form').find('input[type=hidden][name=' + filter + ']');
        const isMultiple = input.data('multiple');

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
        initFiltersForm();
        initScroll();
        initMoreFiltersVisibility();
    }

    module.export({
        initOnPjaxLoad: true,
        init,
        applyFilters,
        selectTag,
        toggleMoreFilters,
    });
});
