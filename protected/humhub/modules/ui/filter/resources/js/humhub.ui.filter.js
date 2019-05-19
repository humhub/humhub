/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('ui.filter', function(module, require, $) {

    var Widget = require('ui.widget').Widget;
    var object = require('util').object;

    var FilterInput = object.extendable(function($node, filter) {
        this.$ = $node;
        this.filter = filter;
    });

    FilterInput.prototype.getValue = function() {/* Abstract function */ };
    FilterInput.prototype.toggle = function() { /* Abstract function */ };
    FilterInput.prototype.isActive = function() { /* Abstract function */ };

    FilterInput.prototype.inputChange = function(evt) {
        this.filter.triggerChange(evt);
    };

    FilterInput.prototype.getId = function() {
        return this.$.data('filter-id');
    };

    FilterInput.prototype.hasCategory = function() {
        return !!this.$.data('filter-category');
    };

    FilterInput.prototype.isMultiple = function() {
        return this.$.data('filter-multiple');
    };

    FilterInput.prototype.getKey = function() {
        return this.hasCategory() ? this.getCategory() : this.getId();
    };

    FilterInput.prototype.getCategory = function() {
        return this.$.data('filter-category') || this.getId();
    };

    var TextInput = FilterInput.extend(function($node, filter) {
        FilterInput.call(this, $node, filter);
        this.delay = object.defaultValue(this.$.data('filter-input-delay'), 500);
    });

    TextInput.prototype.inputChange = function(evt) {
        if (evt.keyCode === 13) {
            evt.preventDefault();
        }

        if (this.getValue() !== this.lastValue) {
            this.lastValue = this.getValue();
            if (this.request) {
                clearTimeout(this.request);
            }

            var that = this;
            this.request = setTimeout(function() {that.filter.triggerChange();}, this.delay);
        }
    };

    TextInput.prototype.getValue = function() {
        return this.$.val();
    };

    TextInput.prototype.isActive = function() {
        return this.getValue() && this.getValue().length
    };

    var CheckBoxInput = FilterInput.extend(function($node, filter) {
        FilterInput.call(this, $node, filter);
        this.$icon = this.$.children('i');
        this.activeClass = object.defaultValue(this.$.data('filter-icon-active'), 'fa-check-square-o');
        this.inActiveClass = object.defaultValue(this.$.data('filter-icon-inactive'), 'fa-square-o');
    });

    CheckBoxInput.prototype.getValue = function() {
        return object.defaultValue(this.$.data('filter-value'), this.getId());
    };

    CheckBoxInput.prototype.isActive = function() {
        return this.$icon.hasClass(this.activeClass);
    };

    CheckBoxInput.prototype.toggle = function() {
        this.$icon.toggleClass(this.inActiveClass).toggleClass(this.activeClass);
        this.filter.triggerChange();
    };

    CheckBoxInput.prototype.deactivate = function() {
        this.$icon.removeClass(this.activeClass).addClass(this.inActiveClass);
    };

    CheckBoxInput.prototype.activate = function() {
        this.$icon.removeClass(this.inActiveClass).addClass(this.activeClass);
    };

    var RadioInput = CheckBoxInput.extend(function($node, filter) {
        CheckBoxInput.call(this, $node, filter);
        this.activeClass = object.defaultValue(this.$.data('filter-icon-active'), 'fa-dot-circle-o');
        this.inActiveClass = object.defaultValue(this.$.data('filter-icon-inactive'), 'fa-circle-o');
    });

    RadioInput.prototype.toggle = function() {
        var wasActive = this.isActive();

        if(this.isActive() && this.isForce()) { // we can't deactivate forced radio fields
            return;
        }

        this.findInputsByGroup(this.getCategory()).forEach(function(radio) {
            radio.deactivate();
        });

        if(!wasActive) {
            !this.activate();
        }

        this.filter.triggerChange();
    };

    RadioInput.prototype.isForce = function() {
        return this.$.data('radio-force');
    };

    RadioInput.prototype.findInputsByGroup = function() {
        var result = [];
        var that = this;
        this.filter.$.find('[data-radio-group="'+this.getRadioGroup()+'"]').each(function() {
            result.push(that.filter.getFilterInput($(this)));
        });
        return result;
    };

    RadioInput.prototype.getRadioGroup = function() {
        return this.$.data('radio-group');
    };

    var PickerInput = FilterInput.extend(function($node, filter) {
        FilterInput.call(this, $node, filter);
    });

    FilterInput.prototype.getValue = function() {
        return Widget.instance(this.$).val()
    };

    FilterInput.prototype.isActive = function() {
        return Widget.instance(this.$).hasValues();
    };

    var filterTypes = {
        'checkbox': CheckBoxInput,
        'radio': RadioInput,
        'picker': PickerInput,
        'text': TextInput
    };

    var addFilterType = function(key, inputClass) {
        filterTypes[key] = inputClass;
    };

    var Filter = Widget.extend();

    Filter.prototype.init = function() {};

    Filter.prototype.toggleFilter = function(evt) {
        var filterInput = this.getFilterInput(evt.$trigger);
        if(filterInput) {
            filterInput.toggle();
        }
    };

    Filter.prototype.inputChange = function(evt) {
        var filterInput = this.getFilterInput(evt.$trigger);
        if(filterInput) {
            filterInput.inputChange(evt);
        } else {
            this.triggerChange();
        }
    };

    Filter.prototype.getFilterInput = function($input) {
        var instance = $input.data('filter-input-instance');

        if(instance) {
            return instance;
        }

        var FilterType = filterTypes[Filter.getFilterType($input)];
        if(FilterType) {
            instance =  new FilterType($input, this);
            $input.data('filter-input-instance', instance);
            return instance;
        }

        return null;
    };

    Filter.prototype.triggerChange = function() {
        this.fire('afterChange');
    };

    Filter.prototype.getFilterMap = function(selector) {
        var result = {};

        this.getFilterInputs().forEach(function(filter) {
            if(filter.isActive()) {
                var filterCategory = result[filter.getKey()];
                if(filter.isMultiple()) {
                    filterCategory = filterCategory || [];
                    filterCategory.push(filter.getValue());
                } else {
                    filterCategory = filter.getValue();
                }

                result[filter.getKey()] = filterCategory;
            }
        });

        return result;
    };

    Filter.prototype.getFilterInputs = function() {
        var result = [];
        var that = this;
        this.$.find('[data-filter-id]').each(function() {
            var input = that.getFilterInput($(this));
            if(input) {
                result.push(input);
            }
        });
        return result;
    };

    Filter.prototype.getFilterById = function(id) {
        return this.getFilterInput($('[data-filter-id = "'+id+'"]'));
    };

    Filter.prototype.isActive = function(key) {
        var filter = this.getFilterById(key);
        return (filter) ? filter.isActive() : false;
    };

    Filter.prototype.getActiveFilterCount = function(options) {
        var count = 0;
        this.getFilterInputs().forEach(function(input) {
            if(input.isActive() && checkFilterAgainstOptions(input, options)) {
                count++;
            }
        });

        return count;
    };

    var checkFilterAgainstOptions = function(input, options) {
        options = options || {};
        var result = true;
        if(options.exclude) {
            if(object.isArray(options.exclude)) {
                result = options.exclude.indexOf(input.getCategory()) <= 0;
            } else {
                result = input.getCategory() !== options.exclude;
            }
        }

        if(options.include) {
            if(object.isArray(options.include)) {
                result = options.include.indexOf(input.getCategory()) >= 0;
            } else {
                result = input.getCategory() === options.include;
            }
        }

        return result;
    };

    var setToArray = function(set) {
        var result = [];
        set.forEach(function(value) {
            result.push(value);
        });
        return result;
    };

    Filter.getFilterType = function($input) {
        return $input.data('filter-type');
    };

    module.export({
        Filter: Filter,
        FilterInput: FilterInput,
        addFilterType: addFilterType
    });
});
