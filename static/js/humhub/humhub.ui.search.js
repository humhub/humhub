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
            list: '.dropdown-search-list',
            form: '.dropdown-search-form',
            input: 'input.dropdown-search-keyword',
            provider: '.dropdown-search-provider',
            providerContent: '.dropdown-search-provider-content',
            providerCounter: '.dropdown-search-provider-title > span',
            additionalToggler: {
                form: 'form[data-toggle="humhub.ui.search"]',
                input: 'input[type=text]:first',
                submit: '[type=submit]'
            }
        }

        $(document).on('click', that.selectors.panel, function (e) {
            e.stopPropagation();
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
        })

        that.initAdditionalToggle();
    }

    Search.prototype.initAdditionalToggle = function () {
        const that = this;
        const form = $(that.selectors.additionalToggler.form);

        if (form.length === 0) {
            return;
        }

        const search = function (keyword) {
            that.getForm().hide();
            that.getInput().val(keyword);
            that.showPanel().search();
        }

        form.find(that.selectors.additionalToggler.submit).on('click', function (e) {
            search($(this).closest('form').find(that.selectors.additionalToggler.input).val());
            return false;
        });

        form.find(that.selectors.additionalToggler.input).on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                search($(this).val());
            }
        });
    }

    Search.prototype.getToggler = function () {
        return this.$.find(this.selectors.toggler);
    }

    Search.prototype.getPanel = function () {
        return this.$.find(this.selectors.panel);
    }

    Search.prototype.getList = function () {
        return this.$.find(this.selectors.list);
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
            this.getToggler().dropdown('toggle');
        }
        return this;
    }

    Search.prototype.menu = function () {
        this.getForm().show();
        this.getInput().focus();
    }

    Search.prototype.search = function () {
        const that = this;

        this.getProviders().each(function () {
            const provider = $(this);
            provider.addClass('provider-searching').show()
                .find(that.selectors.providerCounter).hide();
            loader.set(provider.find(that.selectors.providerContent), {size: '8px', css: {padding: '0px'}});

            that.refreshSize();

            const data = {
                provider: provider.data('provider'),
                keyword: that.getInput().val()
            };
            client.post(module.config.url, {data}).then(function (response) {
                provider.replaceWith(response.html);
                that.refreshSize();
            });
        });
    }

    Search.prototype.refreshSize = function () {
        this.getPanel().css('height', 'auto');
        const maxHeight = $(window).height() - this.getPanel().offset().top - 80;
        if (this.getPanel().height() > maxHeight) {
            this.getPanel().css('height', maxHeight);
        }
    }

    module.export = Search;
});
