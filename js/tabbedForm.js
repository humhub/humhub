$(document).ready(function () {
    /**
     * Prepares all included fieldsets for $form indexed
     * by its label (legend).
     * 
     * @param {type} $form
     * @returns {$lastFieldSet$fieldSet} Array of fieldsets indexed by its label
     */
    var getPreparedFieldSets = function ($form) {
        var result = {};
        var $lastFieldSet;
        
        // Assamble all fieldsets with label
        $form.find('fieldset').each(function () {
            var $fieldSet = $(this).hide();
            
            var legend = $fieldSet.children('legend').text();
            
            // If we have a label we add the fieldset as is else we append its inputs to the previous fieldset
            if (legend && legend.length) {
                result[legend] = $lastFieldSet = $fieldSet;
            } else if($lastFieldSet) {
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
    var hasErrors = function($fieldSet) {
        return $fieldSet.find('.error, .has-error').length > 0;
    };
    
    /**
     * Initialize tabbed forms.
     * Note: this currently does only work with on form per page because of the tab id's
     */
    $('[data-ui-tabbed-form]').each(function () {
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
            
            // init tab structure
            $tabs.append('<li><a href="#tab-' + index + '" data-toggle="tab">' + label + '</a></li>');
            $tabContent.append('<div class="tab-pane" data-tab-index="'+index+'" id="tab-' + index + '"></div>');
            
            // clone inputs from fieldSet into our tab structure
            var $inputs = $fieldSet.children(".form-group");
            $('#tab-' + index).html($inputs.clone());
            
            // remove old fieldset from dom
            $fieldSet.remove();
            
            // change tab on tab key for the last input of each tab
            var tabIndex = index;
            $tabContent.find('.form-control').last().on('keydown', function(e) {
                var keyCode = e.keyCode || e.which; 
                
                if(keyCode === 9) { //Tab
                    var $nextTabLink = $tabs.find('a[href="#tab-' + (tabIndex+1) + '"]');
                    if($nextTabLink.length) {
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
            $form.prepend($errorSummary.clone());
            $errorSummary.remove();
        }
        
        // focus first input on tab change
        $form.find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var tabId = $(e.target).attr('href'); // newly activated tab
            $(tabId).find('.form-control').first().focus();
        });
          

        // activate the first tab or the tab with errors
        $tabs.find('a[href="#tab-' + activeTab + '"]').tab('show'); 
    });
    
    // Make sure frontend validation also activates the tab with errors.
    $(document).on('afterValidate', function(evt, messages, errors) {
        if(errors.length) {
            var index = $(errors[0].container).closest('.tab-pane').data('tab-index');
            $('a[href="#tab-' + index + '"]').tab('show');
        }
    });
});