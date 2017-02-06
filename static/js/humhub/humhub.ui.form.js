humhub.module('ui.form', function(module, require, $) {
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;

    var TabbedForm = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(TabbedForm, Widget);

    TabbedForm.component = 'humhub-tabbed-form';

    TabbedForm.prototype.validate = function() {
        return this.$.is('form');
    };

    TabbedForm.prototype.init = function() {
        this.$.hide();
        var activeTab = 0;

        var $tabContent = $('<div class="tab-content"></div>');
        var $tabs = $('<ul id="profile-tabs" class="nav nav-tabs" data-tabs="tabs"></ul>');
        this.$.prepend($tabContent).prepend($tabs);

        var index = 0;
        $.each(this.getPreparedFieldSets(), function(label, $fieldSet) {

            // activate this tab if there are any errors
            if (_hasErrors($fieldSet)) {
                activeTab = index;
            }

            // init tab structure
            $tabs.append('<li><a href="#tab-' + index + '" data-toggle="tab">' + label + '</a></li>');
            $tabContent.append('<div class="tab-pane" data-tab-index="' + index + '" id="tab-' + index + '"></div>');

            // clone inputs from fieldSet into our tab structure
            var $inputs = $fieldSet.children(".form-group");
            $('#tab-' + index).html($inputs);

            // remove old fieldset from dom
            $fieldSet.remove();

            // change tab on tab key for the last input of each tab
            var tabIndex = index;
            $tabContent.find('.form-control').last().on('keydown', function(e) {
                var keyCode = e.keyCode || e.which;

                if (keyCode === 9) { //Tab
                    var $nextTabLink = $tabs.find('a[href="#tab-' + (tabIndex + 1) + '"]');
                    if ($nextTabLink.length) {
                        e.preventDefault();
                        $nextTabLink.tab('show');
                    }
                }
            });

            index++;
        });

        // prepend error summary to form if exists
        var $errorSummary = $('.errorSummary');
        if ($errorSummary.length) {
            this.$.prepend($errorSummary.clone());
            $errorSummary.remove();
        }

        // focus first input on tab change
        this.$.find('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var tabId = $(e.target).attr('href'); // newly activated tab
            $(tabId).find('.form-control').first().focus();
        });


        // activate the first tab or the tab with errors
        $tabs.find('a[href="#tab-' + activeTab + '"]').tab('show');
        this.$.fadeIn();
    };


    /**
     * Prepares all included fieldsets for $form indexed
     * by its label (legend).
     * 
     * @param {type} $form
     * @returns {$lastFieldSet$fieldSet} Array of fieldsets indexed by its label
     */
    TabbedForm.prototype.getPreparedFieldSets = function() {
        var result = {};
        var $lastFieldSet;

        // Assamble all fieldsets with label
        this.$.find('fieldset').each(function() {
            var $fieldSet = $(this).hide();

            var legend = $fieldSet.children('legend').text();

            // If we have a label we add the fieldset as is else we append its inputs to the previous fieldset
            if (legend && legend.length) {
                result[legend] = $lastFieldSet = $fieldSet;
            } else if ($lastFieldSet) {
                $lastFieldSet.append($fieldSet.children(".form-group"));
            }
        });
        return result;
    };


    /**
     * Check for errors in a specific category.
     * @param _object
     * @returns {boolean}
     */
    var _hasErrors = function($fieldSet) {
        return $fieldSet.find('.error, .has-error').length > 0;
    };

    var init = function() {
        // Make sure frontend validation also activates the tab with errors.
        $(document).on('afterValidate.humhub:ui:tabbedForm', function(evt, messages, errors) {
            if (errors.length && Widget.exists('ui.form.TabbedForm')) {
                var index = $(errors[0].container).closest('.tab-pane').data('tab-index');
                $('a[href="#tab-' + index + '"]').tab('show');
            }
        });
    };

    module.export({
        init: init,
        TabbedForm: TabbedForm
    });
});