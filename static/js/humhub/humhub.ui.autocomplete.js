humhub.module('ui.autocomplete', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var client = require('client');
    var additions = require('ui.additions');
    var util = require('util');
    var object = util.object;

    var Autocomplete = function (node, options) {
        Widget.call(this, node, options);
    };

    Autocomplete.component = 'humhub-ui-autocomplete';

    object.inherits(Autocomplete, Widget);

    Autocomplete.prototype.init = function () {
        var $input = this.$;

        $input.wrap(Autocomplete.template.wrapper);

        var $wrapper = $input.parent();
        var $optionsContainer = $(Autocomplete.template.optionsContainer).appendTo($wrapper);

        $wrapper.on('show.bs.dropdown', function () {
            if($input.val().length < $input.data('min-input')) {
                if ($wrapper.hasClass('open'))
                    $wrapper.removeClass('open');
            }
        });

        $input.on('input change focus', function () {
            client.get($input.data('url'), {
                data: {
                    keyword: $input.val()
                }
            }).then(function (suggestions) {
                $optionsContainer.html('');

                if (suggestions.data.length) {
                    suggestions.data.forEach(function (suggestion) {
                        var $option;

                        if(suggestion.html) {
                            $option = $(suggestion.html).appendTo($optionsContainer);
                        } else {
                            $option = $(Autocomplete.template.option).appendTo($optionsContainer);
                            $option.text(suggestion.text);
                        }

                        $option.on('click', function () {
                            $input.val(suggestion.text);
                        });
                    });

                    if (!$wrapper.hasClass('open')) {
                        $input.dropdown('toggle');
                    }
                } else {
                    $wrapper.removeClass('open');
                }
            })
        });
    };

    Autocomplete.template = {
        option: '<div class="autocomplete-item dropdown-item" role="button"></div>',
        optionImage: '<div class="autocomplete-item-image"></div>',
        wrapper: '<div class="autocomplete-dropdown dropdown"></div>',
        optionsContainer: '<div class="dropdown-menu" style="width: 100%"></div>'
    };

    var init = function () {
        additions.register('ui.autocomplete', '[data-ui-widget="ui.autocomplete.Autocomplete"]', function ($match) {
            $match.each(function () {
                Autocomplete.instance(this);
            });
        });
    };

    module.export({
        init: init,
        Autocomplete: Autocomplete
    });
});
