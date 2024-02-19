humhub.module('ui.search', function(module, require, $) {
    const client = require('client');
    const loader = require('ui.loader');
    const Widget = require('ui.widget').Widget;

    const Search = Widget.extend();

    Search.prototype.init = function() {
        const that = this;

        $(document).on('click', '#dropdown-search', function (e) {
            e.stopPropagation();
        });

        this.$.find('.dropdown-search-keyword').on('keypress', function (e) {
            if (e.which === 13) {
                that.search();
            }
        });

        this.$.find('#dropdown-search').niceScroll({
            cursorwidth: "7",
            cursorborder: "",
            cursorcolor: "#555",
            cursoropacitymax: "0.2",
            nativeparentscrolling: false,
            railpadding: {top: 0, right: 0, left: 0, bottom: 0}
        });
    }

    Search.prototype.menu = function() {
        const keywordInput = this.$.find('.dropdown-search-keyword');
        if (keywordInput.length) {
            keywordInput.focus();
        }
    }

    Search.prototype.search = function() {
        const that = this;

        this.$.find('.dropdown-search-provider').each(function () {
            const provider = $(this);
            provider.addClass('provider-searching').show()
                .find('.dropdown-search-provider-title > span').hide();
            loader.set(provider.find('.dropdown-search-provider-content'), {size: '8px', css: {padding: '0px'}});

            const data = {
                provider: provider.data('provider'),
                keyword: that.$.find('input.dropdown-search-keyword').val()
            };
            client.post(module.config.url, {data}).then(function (response) {
                provider.replaceWith(response.html);
            });
        });
    }

    module.export = Search;
});
