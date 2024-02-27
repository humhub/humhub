humhub.module('ui.search', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');
    const Widget = require('ui.widget').Widget;

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
            providerCounter: '.search-provider-title > span',
            backdrop: '.dropdown-backdrop',
            additionalToggler: {
                form: 'form[data-toggle="humhub.ui.search"]',
                input: 'input[type=text]:first',
                submit: '[type=submit]'
            }
        }

        $(document).on('click', that.selectors.panel, function (e) {
            e.stopPropagation();
        });

        $(document).on('click', that.selectors.close, function () {
            that.getMenuToggler().dropdown('toggle');
        });

        that.getInput().on('keypress', function (e) {
            if (e.which === 13) {
                that.search();
            }
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
            that.refreshSize();
            if (that.getBackdrop().length === 0) {
                that.$.append('<div class="' + that.selectors.backdrop.replace('.', '') + '">');
            }
            if (that.getList().is(':visible')) {
                // refresh NiceScroll after reopen it with searched results
                that.getList().hide().show();
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

        const search = function (keyword) {
            that.getForm().hide();
            that.getInput().val(keyword);
            that.setCurrentToggler(submit);
            that.showPanel().search();
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
        });

        that.$.on('hide.bs.dropdown', function (e) {
            if (input.is(':focus')) {
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

    Search.prototype.isVisiblePanel = function () {
        return this.$.hasClass('open');
    }

    Search.prototype.showPanel = function () {
        if (!this.isVisiblePanel()) {
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
        this.getInput().focus();
    }

    Search.prototype.search = function () {
        const that = this;

        if (that.previousKeyword === that.getInput().val()) {
            that.refreshSize();
            return;
        }

        this.getList().show();

        this.getProviders().each(function () {
            const provider = $(this);

            if (provider.hasClass('provider-searching')) {
                return;
            }

            provider.addClass('provider-searching').show()
                .find(that.selectors.providerCounter).hide();
            loader.set(provider.find(that.selectors.providerContent), {size: '8px', css: {padding: '0px'}});

            that.refreshSize();

            const data = {
                provider: provider.data('provider'),
                keyword: that.getInput().val()
            };
            client.post(module.config.url, {data}).then(function (response) {
                const newProviderContent = $(response.html);
                provider.replaceWith(newProviderContent);
                newProviderContent.find(that.selectors.providerRecord).highlight(data.keyword);
                that.refreshSize();
            });

            that.previousKeyword = data.keyword;
        });
    }

    Search.prototype.refreshSize = function () {
        // Set proper panel height
        const maxHeight = $(window).height() - this.getPanel().offset().top - ($(window).width() > 390 ? 80 : 0);
        this.getPanel().css('height', 'auto');
        if (this.getPanel().height() > maxHeight) {
            this.getPanel().css('height', maxHeight);
        }

        // Centralize panel if it is over window
        const menuTogglerLeft = this.getMenuToggler().offset().left;
        const currentTogglerLeft = this.getCurrentToggler().offset().left;
        const windowWidth = $(window).width();
        const panelWidth = this.getPanel().width();
        let isPanelShifted = false;
        if (menuTogglerLeft === currentTogglerLeft) {
            this.getPanel().css('left', '');
        } else {
            this.getPanel().css('left', currentTogglerLeft - menuTogglerLeft);
            isPanelShifted = true;
        }
        if (this.getPanel().offset().left < 0 || this.getPanel().offset().left + panelWidth > windowWidth) {
            this.getPanel().css('left', -(menuTogglerLeft - (windowWidth - panelWidth) / 2));
            isPanelShifted = true;
        }

        // Set arrow pointer position to current toggler
        if (!isPanelShifted) {
            this.getArrow().css('right', '');
        } else if (currentTogglerLeft === this.getPanel().offset().left) {
            this.getArrow().css('right', panelWidth - 30);
        } else {
            this.getArrow().css('right', panelWidth - currentTogglerLeft - this.getPanel().offset().left + 12);
        }
    }

    module.export = Search;
});
