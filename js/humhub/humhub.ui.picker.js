humhub.initModule('ui.picker', function (module, require, $) {

    var loader = require('ui.loader');
    var additions = require('ui.additions');

    module.initOnPjaxLoad = false;

    var defaultAjaxOptions = {
        delay: 250,
        dataType: 'json',
        type: 'GET'
    };

    var Picker = function (node, cfg) {
        this.cfg = cfg || {};
        this.$ = (node instanceof $) ? node : $(node);

        if (!this.$.is('select')) {
            module.log.warn('Tried to initiate picker instance for non select', node);
        }

        this.$.data('picker', this);
        this.url = this.cfg.url || this.$.data('picker-url');
        this.options = $.extend({}, this.getDefaultOptions(), this.cfg);
        _initSelect2(this.$, this.options);
    };

    Picker.prototype.getDefaultOptions = function (cfg) {
        var that = this;
        return {
            theme: "humhub",
            multiple: true,
            ajax: that.ajaxOptions(),
            templateSelection: $.proxy(that.templateSelection, that),
            templateResult: $.proxy(that.templateResult, that),
            sorter: that.sortResults,
            placeholder: that.$.data('placeholder'),
            placeholderMore: that.$.data('placeholder-more'),
            language: {
                inputTooShort: function (args) {
                    return that.$.data('input-too-short');
                },
                inputTooLong: function (args) {
                    return that.$.data('input-too-long');
                },
                errorLoading: function () {
                    return module.text('error.loadingResult');
                },
                loadingMore: function () {
                    return module.text('showMore');
                },
                noResults: function () {
                    return that.$.data('no-result');
                },
                maximumSelected: function (args) {
                    return that.$.data('maximum-selected');
                }
            }
        };
    };

    /**
     * Returns default ajax options.
     */
    Picker.prototype.ajaxOptions = function () {
        var ajax = $.extend({}, defaultAjaxOptions, this.cfg.ajax);
        ajax.url = this.url;
        ajax.data = this.ajaxData;
        ajax.processResults = $.proxy(this.prepareResult, this);
        return ajax;
    };
    
    /**
     * Mapping between select2 search parameter and our picker parameter.
     * 
     * @param {type} params select2 search parameter
     */
    Picker.prototype.ajaxData = function (params) {
        return {
            keyword: params.term,
            page: params.page
        };
    };

    /**
     * Used to sort the results of the search queries.
     * The sorter takes the following properties into account:
     * 
     *   - Sort Priority
     *   - Item text should match the search term
     *   - IndexOf search term in item text
     *   - Disabled items at the end.
     *   
     * @param {type} results
     * @returns {unresolved}
     */
    Picker.prototype.sortResults = function (results) {
        results.sort(function (a, b) {
            var aQueryIndex = a.text.indexOf(a.term);
            var bQueryInex = b.text.indexOf(a.term);
            if (a.disabled !== b.disabled) {
                return (a.disabled < b.disabled) ? -1 : 1;
            } else if (a.priority !== b.priority) {
                return (a.priority > b.priority) ? -1 : 1;
            } else if (aQueryIndex >= 0 && bQueryInex < 0) {
                return -1;
            } else if (aQueryIndex < 0 && bQueryInex >= 0) {
                return 1;
            } else if (aQueryIndex >= 0 && bQueryInex >= 0) {
                return (aQueryIndex > bQueryInex) ? 1 : -1;
            }

            return 0;
        });
        return results;
    };

    /**
     * Initializes the select2 widget for the given $node with the given $options.
     * 
     * @param {type} $node select field node
     * @param {type} options widget options
     * @returns {undefined}
     */
    var _initSelect2 = function ($node, options) {
        // This is patch for removing select items by backspace see: https://github.com/select2/select2/issues/3354
        $.fn.select2.amd.require(['select2/selection/search'], function (Search) {
            var oldRemoveChoice = Search.prototype.searchRemoveChoice;

            Search.prototype.searchRemoveChoice = function () {
                oldRemoveChoice.apply(this, arguments);
                this.$search.val('');
                $node.select2('close');
            };

            var select2 = $node.select2(options).data('select2');

            select2.$container.on('focusout', function () {
                $node.data('picker').renderPlaceholder();
            });

            // Patch for https://github.com/select2/select2/issues/4614#issuecomment-251277428 strange rendering behaviour
            select2.on('results:message', function (params) {
                this.dropdown._resizeDropdown();
                this.dropdown._positionDropdown();
            });

            // Thefollowing two listeners enables placeholder for non empty selection fields
            $node.on('select2:select', function () {
                $('.tooltip').remove();
                $node.data('picker').renderPlaceholder(true);
            });

            // Get sure the placeholder is active for initial selections
            $node.data('picker').renderPlaceholder(true);
        });
    };

    /**
     * Renders the placeholder if the maximum selection count is not exceeded.
     * @returns {undefined}
     */
    Picker.prototype.renderPlaceholder = function (delayed) {
        if (delayed) {
            var that = this;
            setTimeout(function () {
                that.renderPlaceholder();
            }, 50);
        }

        if (this.$.children(':selected').length >= this.$.data('maximum-selection-length')) {
            this.$.data('select2').$selection.find('input').attr('placeholder', null);
        } else if (this.$.val()) {
            this.$.data('select2').$selection.find('input').attr('placeholder', this.options.placeholderMore);
        } else {
            this.$.data('select2').$selection.find('input').attr('placeholder', this.options.placeholder);
        }
    };

    /**
     * Template function for selected items.
     * 
     * @param {type} data item data
     * @param {type} container
     * @returns {jQuery|$}
     */
    Picker.prototype.templateSelection = function (data, container) {
        var image = data.image || $(data.element).data('image');
        var text = data.text || $(data.element).data('text');

        var $result;
        if (image) {
            $result = $(this.getImageNode(image) + ' <span>' + text + '</span> <i class="fa fa-times-circle picker-close"></i>');
        } else {
            $result = $('<span class="no-image">' + text + '</span> <i class="fa fa-times-circle picker-close"></i>');
        }

        var that = this;
        var $closeButton = $result.filter('.picker-close');
        $closeButton.on('click', function () {
            $(this).siblings('.select2-selection__choice__remove').trigger('click');
            that.deselect(data.id);
        });

        return $result;
    };

    /**
     * Template function for search query results.
     * 
     * @param {type} item
     * @returns {jQuery|$}
     */
    Picker.prototype.templateResult = function (item) {
        // If no item id is given the function was called for the search term.
        if (!item.id) {
            return loader.set($('<div></div>'), {'css': {'padding': '4px'}});
        }

        var title = (item.disabled) ? item.disabledText : '';
        var style = (item.disabled) ? 'margin-right:5px;opacity: 0.4' : 'margin-right:5px;';;
        var $result = $('<a href="#" title="' + title + '" data-placement="right" tabindex="-1" style="'+style+'">' + this.getImageNode(item.image) + ' ' + item.text + '</a>');
        $result.tooltip({html: false, container: 'body', placement: "right"});

        if (item.term) {
            $result.highlight(item.term);
        }

        return $result;
    };

    /**
     * Prepares the image node for.
     * 
     * @param {type} image
     * @returns {String}
     */
    Picker.prototype.getImageNode = function (image) {
        // The image is either an html node itself or just an url
        return (image.indexOf('<') >= 0)
                ? image
                : '<img class="img-rounded" src="' + image + '" alt="24x24" style="width:24px;height:24px;"  height="24" width="24">';
    };

    /**
     * Adds an selection item. If the option is not available yet the option is added
     * to our select field.
     * 
     * @param string id item id
     * @param string text item text
     * @param string image item image
     * @returns {undefined}
     */
    Picker.prototype.select = function (id, text, image) {
        var $option = this.getOption(id);

        // Only select if not already selected
        if ($option.length && $option.is(':selected')) {
            return;
        } else if ($option.length) {
            $option.prop('selected', true);
        } else {
            this.$.append('<option value="' + id + '" data-image="' + (image || '') + '" selected>' + text + '</option>');
            this.$.triggerHandler('change');
            this.renderPlaceholder(true);
        }
    };

    /**
     * Deselects an option with the given id (value)
     * 
     * @param {type} id
     * @returns {undefined}
     */
    Picker.prototype.deselect = function (id) {
        this.getOption(id).remove();
        this.$.trigger('change');
    };

    /**
     * Returns an option node by the given id (value)
     * 
     * @param {type} id
     * @returns {unresolved}
     */
    Picker.prototype.getOption = function (id) {
        return this.$.children().filter(function () {
            return this.value === id;
        });
    };

    Picker.prototype.prepareResult = function (data, params) {
        $.each(data, function (i, item) {
            item.id = item.guid || item.id;
            item.term = params.term;
            // Compatibility with old picker implementation
            item.text = item.text || item.title || item.displayName;
        });
        
        

        //TODO: sort + hide remove already selected.
        return {
            results: data,
            /*pagination: {
             more: (params.page * 30) < data.total_count
             }*/
        };
    };

    var init = function () {
        additions.registerAddition('[data-ui-picker]', function ($match) {
            $match.each(function (i, node) {
                var $node = $(node);
                
                if ($node.data('picker')) {
                    return;
                }
                
                var PickerType = Picker;

                // Try to resolve picker type if given
                var pickerNs = $node.data('ui-picker');
                if (pickerNs && pickerNs.length) {
                    PickerType = require(pickerNs);
                }

                if (!PickerType) {
                    module.log.error('Could not initialize picker for ns: ' + pickerNs);
                    return;
                }

                new PickerType($node);
            });
        });
    };

    module.export({
        init: init,
        Picker: Picker
    });
});