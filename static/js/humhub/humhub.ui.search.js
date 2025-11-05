humhub.module('ui.search', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');
    const Widget = require('ui.widget').Widget;
    const highlightWords = require('ui.additions').highlightWords;

    const Search = Widget.extend();

    Search.prototype.init = function() {
        const that = this;

        that.selectors = {
            toggler: '#search-menu[data-toggle=dropdown]',
            panel: '#dropdown-search',
            close: '#dropdown-search-close',
            list: '.dropdown-search-list',
            arrow: '.dropdown-header > .arrow',
            form: '.dropdown-search-form',
            input: 'input.dropdown-search-keyword',
            provider: '.search-provider',
            providerSearched: '.search-provider.provider-searched',
            providerContent: '.search-provider-content',
            providerRecord: '.search-provider-record',
            providerRecordText: '.search-provider-record-text',
            providerCounter: '.search-provider-title > span',
            providerShowAll: '.search-provider-show-all',
            backdrop: '.dropdown-backdrop',
            additionalToggler: {
                form: 'form[data-toggle="humhub.ui.search"]',
                input: 'input[type=text]:first',
                submit: '[type=submit]'
            }
        }

        $(document).on('pjax:end', function () {
            that.reset();
        });

        $(document).on('click', that.selectors.panel, function (e) {
            e.stopPropagation();
        });

        $(document).on('click', that.selectors.close + ', '
            + that.selectors.providerRecord + ', '
            + that.selectors.providerShowAll, function () {
            that.getMenuToggler().dropdown('toggle');
        });

        that.getInput().on('keypress', function (e) {
            if (e.which === 13) {
                that.search();
            }
        }).on('keyup', function () {
            that.searchTimeout(() => that.search(true));
        }).on('keydown', function (e) {
            return that.switchFocus(e.currentTarget.tagName, e.which);
        });

        that.getList().on('keypress', function () {
            that.getCurrentInput().focus();
        }).on('keydown', function (e) {
            return that.switchFocus(e.currentTarget.tagName, e.which);
        });

        that.getList().niceScroll({
            cursorwidth: '7',
            cursorborder: '',
            cursorcolor: '#555',
            cursoropacitymax: '0.2',
            nativeparentscrolling: false,
            railpadding: {top: 0, right: 0, left: 0, bottom: 0}
        });

        that.$.on('shown.bs.dropdown', function () {
            that.refreshPositionSize();
            if (that.getBackdrop().length === 0) {
                that.$.append('<div class="' + that.selectors.backdrop.replace('.', '') + '">');
            }
            if (that.getList().is(':visible')) {
                // refresh NiceScroll after reopen it with searched results
                that.getList().hide().show();
            }
            if (that.getInput().is(':visible')) {
                that.getInput().focus();
            }
        })

        that.initAdditionalToggle();
    }

    Search.prototype.initAdditionalToggle = function () {
        const that = this;
        const form = $(that.selectors.additionalToggler.form);

        if (form.length === 0) {
            return;
        }

        const input = form.find(that.selectors.additionalToggler.input);
        const submit = form.find(that.selectors.additionalToggler.submit);

        const search = function (keyword, forceCurrentSearching) {
            that.getForm().hide();
            that.getInput().val(keyword);
            that.setCurrentToggler(submit);
            that.showPanel().search(forceCurrentSearching);
        }

        submit.on('click', function () {
            search(input.val());
            return false;
        });

        input.on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                search($(this).val());
            }
        }).on('keyup', function () {
            that.searchTimeout(() => search($(this).val(), true));
        }).on('keydown', function (e) {
            return that.switchFocus(e.currentTarget.tagName, e.which);
        });

        that.$.on('hide.bs.dropdown', function (e) {
            if (input.is(':focus') && input.val().trim() !== '') {
                e.preventDefault();
                if (that.getBackdrop().length === 0) {
                    that.$.append('<div class="' + that.selectors.backdrop.replace('.', '') + '">');
                }
            }
        })
    }

    Search.prototype.setCurrentToggler = function (toggleElement) {
        return this.currentToggler = toggleElement;
    }

    Search.prototype.getCurrentToggler = function () {
        return typeof(this.currentToggler) === 'undefined'
            ? this.$.find(this.selectors.toggler)
            : this.currentToggler;
    }

    Search.prototype.getMenuToggler = function () {
        return this.$.find(this.selectors.toggler);
    }

    Search.prototype.getBackdrop = function () {
        return this.$.find(this.selectors.backdrop);
    }

    Search.prototype.getPanel = function () {
        return this.$.find(this.selectors.panel);
    }

    Search.prototype.getList = function () {
        return this.$.find(this.selectors.list);
    }

    Search.prototype.getArrow = function () {
        return this.$.find(this.selectors.arrow);
    }

    Search.prototype.getProviders = function () {
        return this.$.find(this.selectors.provider);
    }

    Search.prototype.getForm = function () {
        return this.$.find(this.selectors.form);
    }

    Search.prototype.getInput = function () {
        return this.$.find(this.selectors.input);
    }

    Search.prototype.hasInput = function () {
        const input = this.getInput();
        return input.length === 1 && input.is(':visible');
    }

    Search.prototype.getAdditionalInput = function () {
        return $(this.selectors.additionalToggler.form + ' ' + this.selectors.additionalToggler.input);
    }

    Search.prototype.hasAdditionalInput = function (isVisible) {
        const input = this.getAdditionalInput();
        if (input.length === 0) {
            return false;
        }
        return typeof isVisible === 'undefined' || isVisible ? input.is(':visible') : !input.is(':visible');
    }

    Search.prototype.getCurrentInput = function () {
        return this.hasAdditionalInput() ? this.getAdditionalInput() : this.getInput();
    }

    Search.prototype.isVisiblePanel = function () {
        return this.$.hasClass('open');
    }

    Search.prototype.showPanel = function () {
        if (!this.isVisiblePanel()) {
            this.getMenuToggler().dropdown('toggle');
        }
        return this;
    }

    Search.prototype.hidePanel = function () {
        if (this.isVisiblePanel()) {
            this.getMenuToggler().dropdown('toggle');
        }
        return this;
    }

    Search.prototype.isSearched = function () {
        return this.$.find(this.selectors.providerSearched).length > 0;
    }

    Search.prototype.menu = function () {
        this.setCurrentToggler(undefined);
        this.getForm().show();
    }

    Search.prototype.searchTimeout = function (searchFunction) {
        const that = this;

        if (typeof that.currentSearchTimeout !== 'undefined') {
            clearTimeout(that.currentSearchTimeout);
        }

        that.currentSearchTimeout = setTimeout(function () {
            that.currentSearchTimeout = undefined;
            searchFunction();
        }, 500);

        // Run this only to display a search loader immediately,
        // The real search process will be rejected while currentSearchTimeout is set
        searchFunction();
    }

    Search.prototype.search = function (forceCurrentSearching) {
        const that = this;
        const data = {
            provider: null,
            keyword: that.getInput().val().trim()
        };

        if (data.keyword === '') {
            that.getList().hide();
            that.getInput().val('');
            that.previousKeyword = '';
            that.refreshPositionSize();
            if (!that.hasInput()) {
                that.hidePanel();
            }
            return;
        }

        if (that.previousKeyword === data.keyword) {
            this.getProviders().each(function () {
                const provider = $(this);
                provider.removeClass('provider-searching');
                loader.reset(provider.find(that.selectors.providerContent));
            });
            that.refreshPositionSize();
            return;
        }

        if (that.hasAdditionalInput(false)) {
            that.getAdditionalInput().val(data.keyword);
        }

        this.getList().show();

        this.getProviders().each(function () {
            const provider = $(this);

            if (!forceCurrentSearching && provider.hasClass('provider-searching')) {
                return;
            }

            provider.addClass('provider-searching').show()
                .find(that.selectors.providerCounter).hide();
            loader.set(provider.find(that.selectors.providerContent), {size: '8px', css: {padding: '0px'}});

            that.refreshPositionSize();

            if (typeof that.currentSearchTimeout !== 'undefined') {
                // Don't run the search process while time is not expired,
                // This action was called only to display the search loader
                return;
            }

            data.provider = provider.data('provider');
            data.route = provider.data('provider-route');
            client.post(module.config.url, {data}).then(function (response) {
                if (data.keyword !== that.getInput().val().trim()) {
                    // Skip this request because other with new keyword was sent
                    return;
                }

                // Prepare and set new content
                const newProviderContent = $(response.html);
                provider.replaceWith(newProviderContent);
                const records = newProviderContent.find(that.selectors.providerRecord);
                if (records.length) {
                    highlightWords(records.find(that.selectors.providerRecordText), data.keyword);
                } else if (newProviderContent.data('hide-on-empty') !== undefined) {
                    newProviderContent.hide();
                }

                that.refreshPositionSize();
            }).catch(function (e) {
                module.log.error(e, true);
                loader.reset(provider.find(that.selectors.providerContent));
                provider.hide();
                that.refreshPositionSize();
            });

            that.previousKeyword = data.keyword;
        });
    }

    Search.prototype.reset = function () {
        this.getCurrentInput().val('');
        this.getProviders().hide();
        this.hidePanel();
    }

    Search.prototype.refreshPositionSize = function () {
        // Set proper top position when additional toggler is used instead of original/main
        this.getPanel().css('top', this.getMenuToggler().css('visibility') === 'hidden'
            ? this.getCurrentToggler().position().top + this.getCurrentToggler().outerHeight() + this.getArrow().outerHeight() - 5
            : '');

        // Set proper panel height
        const panelTop = this.getPanel().position().top + this.$.offset().top - $(window).scrollTop();
        const maxHeight = $(window).height() - panelTop - ($(window).width() > 440 ? 80 : 0);
        this.getPanel().css('height', 'auto');
        if (this.getPanel().height() > maxHeight) {
            this.getPanel().css('height', maxHeight);
        }

        // Centralize panel if it is over window
        const menuTogglerLeft = this.getMenuToggler().offset().left;
        const currentTogglerLeft = this.getCurrentToggler().offset().left;
        const windowWidth = Math.round($(window).width());
        let panelWidth = Math.round(this.getPanel().width());
        if (panelWidth > windowWidth) {
            panelWidth = windowWidth;
            this.getPanel().width(panelWidth);
        }
        this.getPanel().css('left', menuTogglerLeft === currentTogglerLeft ? '' : currentTogglerLeft - menuTogglerLeft);
        if (this.getPanel().offset().left < 0 || this.getPanel().offset().left + panelWidth > windowWidth) {
            this.getPanel().css('left', -(menuTogglerLeft - (windowWidth - panelWidth) / 2));
        }
        if (this.getPanel().offset().left < 0) {
            this.getPanel().css('left', (windowWidth - panelWidth) / 2);
        }

        // Set arrow pointer position to current toggler
        this.getArrow().css('right', panelWidth - (currentTogglerLeft - this.getPanel().offset().left) - 30);
    }

    Search.prototype.switchFocus = function (tag, key) {
        if (key !== 38 && key !== 40) {
            return true;
        }

        const dir = key === 38 ? 'up' : 'down';
        const links = this.getList().find('a:visible');

        if (tag === 'INPUT') {
            if (dir === 'down') {
                links.first().focus();
                return false;
            } else if (dir === 'up') {
                links.last().focus();
                return false;
            }
        } else if (tag === 'UL') {
            if ((dir === 'down' && links.last().is(':focus')) ||
                (dir === 'up' && links.first().is(':focus'))) {
                this.getCurrentInput().focus();
                return false;
            }
        }

        return true;
    }

    module.export = Search;
});
