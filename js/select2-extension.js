//This file contains style alignments for the select2 multi dropdown js framework.
var checkForMultiSelectDropDowns = function() {
    //We have to overwrite the the result gui after every change
    $('.multiselect_dropdown').select2({width: '100%'}).on('change', function () {
        $(this).trigger('update');
    }).on('select2:open', function () {
        $(this).data('isOpen', true);
    }).on('select2:close', function () {
        $(this).data('isOpen', false);
    }).on('update', function () {
        var $container = $(this).next('.select2-container');
        var $choices = $container.find('.select2-selection__choice');
        $choices.addClass('userInput');
        var $closeButton = $('<i class="fa fa-times-circle"></i>');
        $closeButton.on('click', function () {
            $(this).siblings('span[role="presentation"]').trigger('click');
        });
        $choices.append($closeButton);
    });

//For highlighting the input
    $(".select2-container").on("focusin", function () {
        $(this).find('.select2-selection').addClass('select2-selection--focus');
    });

//Since the focusout of the ontainer is called when the dropdown is opened we have to use this focusout
    $(document).off('focusout', '.select2-search__field').on('focusout', '.select2-search__field', function () {
        if (!$(this).closest('.select2-container').prev('.multiselect_dropdown').data('isOpen')) {
            $(this).closest('.select2-selection').removeClass('select2-selection--focus');
        }
    });

    $('.multiselect_dropdown').trigger('update');
}

$(document).ready(function () {
    $.fn.select2.defaults = {};
    checkForMultiSelectDropDowns();
});