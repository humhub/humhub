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

    const _initContainerMoreFilters = function($container) {
        const $toggler = $container.find('.form-search-action-toggle-more');
        if (!$toggler.length) return;

        const $moreFilter = $container.find('.collapse');
        if ($moreFilter.length > 0) {
            $moreFilter[0].addEventListener('hidden.bs.collapse', event => {
                updateMoreFiltersState($container, false);
            });
            $moreFilter[0].addEventListener('shown.bs.collapse', event => {
                updateMoreFiltersState($container, true);
            });
        }

        const isVisible = $toggler.is(':visible');
        $container.data('toggler-visible', isVisible);

        if (isVisible) {
            const initState = getMoreFiltersState($container) && $container.find('.form-search-action-reset').length > 0;
            _toggleContainerMoreFilters($container, initState, false);
        }
    }

    const _handleContainerMoreFiltersResize = function($container) {
        const $toggler = $container.find('.form-search-action-toggle-more');
        if (!$toggler.length) return;

        const isVisiblePrevious = $container.data('toggler-visible') || false;
        const isVisibleCurrent = $toggler.is(':visible');

        if (!isVisibleCurrent && $container.find('.collapse:hidden').length) {
            _toggleContainerMoreFilters($container, true, false);
        }

        if (isVisiblePrevious !== isVisibleCurrent && isVisibleCurrent) {
            _toggleContainerMoreFilters($container, getMoreFiltersState($container));
        }

        $container.data('toggler-visible', isVisibleCurrent);
    }

    const _toggleContainerMoreFilters = function($container, show, updateState = true) {
        const $toggler = $container.find('.form-search-action-toggle-more .btn');
        const $moreFilters = $container.find($toggler.data('bs-target'));
        $toggler.toggleClass('collapsed', !show);
        $moreFilters.toggleClass('show', show);
        if (updateState) {
            updateMoreFiltersState($container, show);
        }
    }

    const initMoreFiltersVisibility = function() {
        $('.container-cards').filter(function() {
            return !$(this).closest('.modal').length;
        }).each(function() {
            _initContainerMoreFilters($(this));
        });

        $(window).on('resize', function() {
            $('.container-cards').filter(function() {
                return !$(this).closest('.modal').length;
            }).each(function() {
                _handleContainerMoreFiltersResize($(this));
            });
        });

        $(document).on('shown.bs.modal', '.modal.show', function() {
            $(this).find('.container-cards').each(function() {
                _initContainerMoreFilters($(this));
            });
        });
    }

    const getMoreFiltersStates = function () {
        const states = window.localStorage.getItem('cards-more-filters');
        return states ? JSON.parse(states) : {};
    }

    const getMoreFiltersState = function ($container, defaultState = false) {
        const states = getMoreFiltersStates();
        const page = getMoreFiltersPage($container);
        return typeof(states[page]) === 'undefined' ? defaultState : states[page];
    }

    const getMoreFiltersPage = function ($container) {
        const $form = $container
            ? $container.find('.form-search-action-toggle-more').closest('form')
            : $('.form-search-action-toggle-more').closest('form');
        return $form.attr('action').replace(/^\/*(.+)\/*$/, '$1');
    }

    const updateMoreFiltersState = function ($container, state) {
        const states = getMoreFiltersStates();
        states[getMoreFiltersPage($container)] = state;
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
    });
});
