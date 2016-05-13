$(document).ready(function () {
    /**
     * Searches a
     * @param {type} $form
     * @returns {$lastFieldSet$fieldSet}
     */
    var getPreparedFieldSets = function ($form) {
        var result = {};
        
        // Assamble all fieldsets with label
        $form.find('fieldset').each(function () {
            var $fieldSet = $(this);
            $fieldSet.hide();
            
            var legend = $fieldSet.children('legend').text();
            if (legend && legend.length) {
                // Make sure all fieldsets are direct children
                result[legend] = $fieldSet;
            }
        });
        return result;
    };
    
    /**
     * Check for errors in a specific category
     * @param _object
     * @returns {boolean}
     */
    var hasErrors = function($fieldSet) {
        var hasError = false;

        $fieldSet.children(".form-group").each(function (index, value) {

            // if an input have the class "error"
            if ($(this).children('.form-control').hasClass("error")) {
                hasError = true;
                return false; // stop loop/function
            }
        });
        return hasError;

    };
    
    $('.tabbed-form').each(function () {
        var activeTab = 0;
        
        var $form = $(this);
        var $tabContent = $('<div class="tab-content"></div>');
        var $tabs = $('<ul id="profile-tabs" class="nav nav-tabs" data-tabs="tabs"></ul>');
        $form.prepend($tabContent);
        $form.prepend($tabs);
        
        var index = 0;
        $.each(getPreparedFieldSets($form), function(label, $fieldSet) {
            // activate this tab if there are any errors
            if (hasErrors($fieldSet)) {
                activeTab = index;
            }
            
            // build tab structure
            $tabs.append('<li><a href="#tab-' + index + '" data-toggle="tab">' + label + '</a></li>');
            $tabContent.append('<div class="tab-pane" data-tab-index="'+index+'" id="tab-' + index + '"></div>');
            
            // clone inputs from fieldSet into our tab structure
            var $inputs = $fieldSet.children(".form-group");
            $('#tab-' + index).html($inputs.clone());
            
            // Remove old fieldset
            $fieldSet.remove();
            
            index++;
        });
        
        // prepend error summary to form if present
        if ($('.errorSummary').length != null) {
            var _errorSummary = $('.errorSummary').clone();
            $('.errorSummary').remove();
            $form.prepend(_errorSummary);
        }

        // activate the first tab or the tab with errors
        $tabs.find('a[href="#tab-' + activeTab + '"]').tab('show');
    });
    
    $(document).on('afterValidate', function(evt, messages, errors) {
        if(errors.length) {
            var index = $(errors[0].container).closest('.tab-pane').data('tab-index');
            $('a[href="#tab-' + index + '"]').tab('show');
        }
    });
});