humhub.module('ui.picker', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var loader = require('ui.loader');
    var additions = require('ui.additions');
    var util = require('util');
    var object = util.object;
    var string = util.string;

    var Picker = function (node, options) {
        Widget.call(this, node, options);
    };

    Picker.component = 'humhub-ui-picker';

    object.inherits(Picker, Widget);

    Picker.prototype.init = function () {
        if (this.options.pickerUrl) {
            this.options.ajax = this.ajaxOptions();
            if (this.options.defaultResults && this.options.defaultResults.length) {
                this.options.dataAdapter = $.fn.select2.amd.require('select2/data/extended-ajax');
            }
        }
        _initSelect2(this.$, this.options);
    };

    Picker.prototype.validate = function () {
        return this.$.is('select');
    };

    Picker.prototype.getDefaultOptions = function () {
        var that = this;
        return {
            theme: "humhub",
            multiple: true,
            templateSelection: $.proxy(that.templateSelection, that),
            templateResult: $.proxy(that.templateResult, that),
            sorter: that.sortResults,
            language: {
                inputTooShort: function () {
                    return that.$.data('input-too-short');
                },
                inputTooLong: function () {
                    return that.$.data('input-too-long');
                },
                errorLoading: function () {
                    // Aborted requests currently would trigger this message, so we can't make us of it...
                    // https://github.com/select2/select2/issues/4355
                    module.log.error('Error Loading Picker result! The request may just has been aborted.');
                    return loader.set($('<div></div>'), {'css': {'padding': '4px'}});
                },
                loadingMore: function () {
                    return module.text('showMore');
                },
                noResults: function () {
                    return that.$.data('no-result');
                },
                maximumSelected: function () {
                    return that.$.data('maximum-selected');
                }
            }
        };
    };

    /**
     * Returns default ajax options.
     */
    Picker.prototype.ajaxOptions = function () {
        var defaultOptions = {
            delay: 250,
            dataType: 'json',
            type: 'GET',
            url: this.options.pickerUrl,
            data: this.ajaxData,
            processResults: $.proxy(this.prepareResult, this),
        };
        return $.extend(defaultOptions, this.options.ajax);
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
            if (a.disabled !== b.disabled) {
                return (a.disabled < b.disabled) ? -1 : 1;
            } else if(a.new !== b.new) {
                return (a.new < b.new) ? -1 : 1;
            } else if (a.priority !== b.priority) {
                return (a.priority > b.priority) ? -1 : 1;
            } else {
                var aQueryIndex = a.text.indexOf(a.term);
                var bQueryIndex = b.text.indexOf(a.term);
                if (aQueryIndex !== bQueryIndex) {
                    return (aQueryIndex > bQueryIndex) ? -1 : 1;
                }
                return 0;
            }
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
        // This is a patch for removing select items by backspace see: https://github.com/select2/select2/issues/3354
        $.fn.select2.amd.require(['select2/selection/search'], function (Search) {
            var oldRemoveChoice = Search.prototype.searchRemoveChoice;

            Search.prototype.searchRemoveChoice = function () {
                oldRemoveChoice.apply(this, arguments);
                this.$search.val('');
                $node.select2('close');
            };

            var select2 = $node.select2(options).data('select2');

            // Get sure our placeholder is rendered when focus out
            select2.$container.on('focusout', function () {
                Widget.instance($node).renderPlaceholder();
            });

            // Patch for https://github.com/select2/select2/issues/4614#issuecomment-251277428 strange rendering behaviour
            select2.on('results:message', function (params) {
                this.dropdown._resizeDropdown();
                this.dropdown._positionDropdown();
            });

            // Thefollowing two listeners enables placeholder for non empty selection fields
            $node.on('select2:select', function () {
                $('.tooltip').remove();
                Widget.instance($node).renderPlaceholder(true);
            }).on('select2:close', function () {
                $('.tooltip').remove();
            });

            // Get sure the placeholder is active for initial selections
            Widget.instance($node).renderPlaceholder(true);

            // Focus if auto focus is active
            if ($node.data('picker-focus')) {
                Widget.instance($node).focus();
            }
        });
    };

    Picker.prototype.focus = function () {
        this.$.select2('focus');
    };

    /**
     * Renders the placeholder if the maximum selection count is not exceeded.
     *
     * @returns {undefined}
     */
    Picker.prototype.renderPlaceholder = function (delayed) {
        if (delayed) {
            var that = this;
            setTimeout(function () {
                that.renderPlaceholder();
            }, 50);
            return;
        }

        if (this.$.children(':selected').length >= this.$.data('maximum-selection-length')) {
            this.$.data('select2').$selection.find('input').attr('placeholder', null).attr('title', null);
        } else if (this.$.val()) {
            this.$.data('select2').$selection.find('input').attr('placeholder', this.options.placeholderMore).attr('title', this.options.placeholderMore);
        } else {
            this.$.data('select2').$selection.find('input').attr('placeholder', this.options.placeholder).attr('title', this.options.placeholderMore);
        }
    };

    Picker.template = {
        selectionWithImage: '{imageNode}<span class="picker-text with-image"></span> <i class="fa fa-times-circle picker-close"></i>',
        selectionNoImage: '<span class="picker-text no-image"></span> <i class="fa fa-times-circle picker-close"></i>',
        result: '<a href="#" tabindex="-1" style="margin-right:5px;">{imageNode} <span class="picker-text"></span></a>',
        resultDisabled: '<a href="#" title="{disabledText}" data-placement="right" tabindex="-1" style="margin-right:5px;opacity: 0.4;cursor:not-allowed">{imageNode} <span class="picker-text"></span></a>',
        imageNode: '<img class="img-rounded" src="{image}" alt="" style="width:24px;height:24px;"  height="24" width="24">',
        imageIcon: '<i class="fa {image}"></i> ',
        option: '<option value="{id}" data-image=\'{image}\' selected></option>',
    };

    /**
     * Called after ajax result are received.
     *
     * @param data
     * @param params
     * @returns {{results: *}}
     */
    Picker.prototype.prepareResult = function (data, params) {
        var that = this;

        $.each(data, function (i, item) {
            item.term = params.term;
            if (that.isDisabledItem(item)) {
                item.disabled = true;
            }
            // Compatibility with old picker implementation and data attributes
            item.id = item.guid || item.id || item['data-id'];
            item.text = item.text || item.title || item.displayName || item['data-text'];
            item.image = item.image || item['data-image'];
            item.new = false;
        });

        var encodedTerm =  string.encode(params.term);

        if(encodedTerm && encodedTerm.length >= that.options.minimumInputLength &&
            that.options.addOptions &&
            $(data).filter(function() {return this.text.localeCompare(encodedTerm)=== 0}).length === 0) {

            data.push({
                'id': '_add:'+params.term,
                'text': module.text('addOption')+' \''+encodedTerm+'\'',
                'textValue': params.term,
                'image': '<i class="fa fa-plus-circle" aria-hidden="true"></i>',
                'new': true
            });
        }

        return {
            results: data,
            /*pagination: {more: (params.page * 30) < data.total_count}*/
        };
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

        this.prepareItem(item);

        var template = (item.disabled) ? Picker.template.resultDisabled : Picker.template.result;

        var $result = $(string.template(template, item))
                .tooltip({html: false, container: 'body', placement: "bottom"})
                .on('click', function (evt) {
                    evt.preventDefault();
                });

        $result.find('.picker-text').text(item.text);

        if (item.term) {
            $result.highlight(item.term);
        }

        return $result;
    };

    /**
     * Template function for selected items.
     *
     * @param {type} item item data
     * @param {type} container
     * @returns {jQuery|$}
     */
    Picker.prototype.templateSelection = function (item, container) {
        this.prepareItem(item);

        var selectionTmpl = (item.image && !item.new) ? Picker.template.selectionWithImage : Picker.template.selectionNoImage;

        var $result = $(string.template(selectionTmpl, item));

        var test = $result.find('.picker-text');
        $result.filter('.picker-text').text(item.text);

        // Initialize item close button
        var that = this;
        $result.filter('.picker-close').on('click', function () {
            $(this).siblings('.select2-selection__choice__remove').trigger('click');
        });

        return $result;
    };

    Picker.prototype.prepareItem = function (item) {
        item.text = item.textValue || item.text || $(item.element).data('text');
        item.image = item.image || $(item.element).data('image');
        item.imageNode = this.getImageNode(item);
        item.disabledText = item.disabledText || '';
    }

    /**
     * Prepares the image node.
     *
     * @param {type} item
     * @returns {String}
     */
    Picker.prototype.getImageNode = function (item) {
        var image = item.image || $(item.element).data('image');

        if (!image) {
            return '';
        }

        if(image.indexOf('<') >= 0) {
            return image;
        } else if(image.indexOf('fa-') === 0) {
            return string.template(Picker.template.imageIcon, item);
        } else {
            return string.template(Picker.template.imageNode, item);
        }

        // The image is either an html node itself or just an url
        return (image.indexOf('<') >= 0) ? image : string.template(Picker.template.imageNode, item);
    };


    /**
     * Adds a selection item. If the option is not available yet the option is added
     * to our select field.
     *
     * @param {string} id item id
     * @param {string} text item text
     * @param {string} image item image
     * @returns {undefined}
     */
    Picker.prototype.select = function (id, text, image, options) {
        options = options || {};
        var $option = this.getOption(id);

        // Only select if not already selected
        if ($option.length && $option.is(':selected')) {
            return false;
        } else if ($option.length) {
            $option.prop('selected', true);
        } else {
            this.$.append($(string.template(Picker.template.option, {
                id: id,
                image: image || ''
            })).text(text));
        }

        if(options.triggerChange !== false) {
            this.triggerChange();
        }

        return true;
    };

    Picker.prototype.triggerChange = function () {
        this.$.triggerHandler('change');
        this.renderPlaceholder(true);
    }

    Picker.prototype.setSelection = function (selection, translate) {
        var that = this;
        var changed = false;
        var vals = this.val();

        this.clear(false);

        selection.forEach(function(item) {
            if(translate) {
                item = translate.call(this, item);
            }

            if(item && that.select(item.id, item.text, item.image, {triggerChange: false}) && vals.indexOf(item.id) < 0) {
                changed = true;
            }
        });

        vals.forEach(function(id) {
            if(!that.isSelected(id)) {
                changed = true;
            }
        });

        if(changed) {
            that.triggerChange();
        }
    };

    Picker.prototype.isSelected = function (id) {
        var values = this.val();
        return values.indexOf(id) >= 0;
    }

    Picker.prototype.remove = function (id) {
        var values = this.val();
        if(this.isSelected(id)) {
            values.splice(values.indexOf(id), 1);
            this.$.val(values).trigger('change');
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

    Picker.prototype.clear = function (triggerChange) {
        this.$.val(null);

        if(triggerChange !== false) {
            this.triggerChange();
        }

        return this;
    };

    Picker.prototype.disable = function($disable) {
        $disable = (!object.isDefined($disable)) ? true : $disable;
        this.$.prop('disabled', $disable);
    };

    /**
     * Returns an option node by the given id (value)
     *
     * @param {type} id
     * @returns {unresolved}
     */
    Picker.prototype.getOption = function (id) {
        return this.$.children().filter(function () {
            return this.value == id;
        });
    };

    Picker.prototype.reset = function () {
        this.$.val('');
        this.$.trigger('change');
    };

    Picker.prototype.val = function() {
        return this.$.val();
    };

    Picker.prototype.hasValue = function(value) {
        var values = this.val();
        return values && values.indexOf(value) >= 0;
    };

    Picker.prototype.hasValues = function(value) {
        var values = this.val();
        return values && values.length;
    }

    Picker.prototype.data = function() {
        return this.$.select2('data');
    };

    Picker.prototype.map = function() {
        var val = this.val();

        if(!val) {
            return {};
        }

        if(!Array.isArray(val)) {
            val = [val];
        }

        var result = {};
        var that = this;
        val.forEach(function(value) {
            result[value] = that.getOption(value).text();
        })

        return result;
    };

    Picker.prototype.isDisabledItem = function (item) {
        return (this.options.disabledItems && object.isArray(this.options.disabledItems) && this.options.disabledItems.indexOf(item.id) >= 0);
    };

    var init = function () {
        additions.register('ui.picker', '.multiselect_dropdown', function ($match) {
            $match.each(function () {
                Picker.instance(this);
            });
        });

        // This extension allows preselected values in our picker if the mininput value is not exceded.
        // http://stackoverflow.com/questions/33080739/select2-default-options-with-ajax
        // https://gist.github.com/govorov/3ee75f54170735153349b0a430581195
        $.fn.select2.amd.define('select2/data/extended-ajax', ['./ajax', '../utils', './minimumInputLength'], function (AjaxAdapter, Utils, MinimumInputLength) {

            function ExtendedAjaxAdapter($element, options) {
                //we need explicitly process minimumInputLength value
                //to decide should we use AjaxAdapter or return defaultResults,
                //so it is impossible to use MinimumLength decorator here
                this.minimumInputLength = options.get('minimumInputLength');
                this.defaultResults = options.get('defaultResults');

                ExtendedAjaxAdapter.__super__.constructor.call(this, $element, options);
            }

            Utils.Extend(ExtendedAjaxAdapter, AjaxAdapter);

            //override original query function to support default results
            var originQuery = AjaxAdapter.prototype.query;

            ExtendedAjaxAdapter.prototype.query = function (params, callback) {
                var defaultResults = (typeof this.defaultResults == 'function') ? this.defaultResults.call(this) : this.defaultResults;
                if (defaultResults && defaultResults.length && (!params.term || params.term.length < this.minimumInputLength)) {
                    if (!params.term || !params.term.length) {
                        var processedResults = this.processResults(defaultResults, params);
                        callback(processedResults);
                        return;
                    }

                    // If search term
                    var filterResult = [];
                    $.each(defaultResults, function (index, item) {
                        if (item['data-text'].toLowerCase().indexOf(params.term.toLowerCase()) >= 0) {
                            filterResult.push(item);
                        }
                    });

                    if (filterResult.length) {
                        var processedResults = this.processResults(filterResult, params);
                        callback(processedResults);
                    } else {
                        this.container.$results.empty();
                    }

                    var $message = $('<li role="treeitem" aria-live="assertive" class="select2-results__option select2-results__message">' + this.options.get('inputTooShort') + '</li>');
                    this.container.$results.prepend($message);
                } else {
                    originQuery.call(this, params, callback);
                }
            };

            return ExtendedAjaxAdapter;
        });
    };

    module.export({
        init: init,
        sortOrder: 100,
        Picker: Picker
    });
});
