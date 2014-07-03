$(document).ready(function() {

    $('.hhtml-datetime-field').each(function(index, element) {
        var $element = $(element)
                , dateTimepickerDefaultOptions = {
                    pickDate: true,
                    pickTime: false,
                    useMinutes: true,
                    useSeconds: false,
                    showToday: true,
                    language: 'en',
                    use24hours: true,
                    sideBySide: false,
                    format: 'YYYY-MM-DD hh:mm'
                }
        , $dateInput = $element.clone()
                , dateTimepickerOptions = {}
        , re_dataAttr = /^data\-options\-(.+)$/
                ;

        $dateInput.removeAttr('name').removeAttr('id');

        $.each(element.attributes, function(index, attr) {
            if (re_dataAttr.test(attr.nodeName)) {
                var key = attr.nodeName.match(re_dataAttr)[1]
                        , nodeValue = attr.nodeValue
                        ;
                nodeValue = nodeValue === 'true' ? true : nodeValue;
                nodevalue = nodeValue === 'false' ? false : nodeValue;
                dateTimepickerOptions[key] = nodeValue;
            }
        });

        if ($element.attr('data-options-pickTime') == "true") {
            dateTimepickerOptions.pickTime = true;
        }

        dateTimepickerOptions = $.extend({}, dateTimepickerDefaultOptions, dateTimepickerOptions);

        $dateInput.datetimepicker(dateTimepickerOptions);
        var datepicker = $dateInput.data("DateTimePicker");

        if (typeof $element.attr('data-options-displayFormat') === "undefined") {
            if (!dateTimepickerOptions.pickTime) {
                datepicker.format = "DD.MM.YYYY";
            } else {
                datepicker.format = "DD.MM.YYYY - HH:mm";
            }
        } else {
            datepicker.format = $element.attr('data-options-displayFormat');
        }

        if ($element.val() != "") {
            datepicker.setDate(datepicker.date); // update visible date to correct Format
        }

        $dateInput.bind('blur change', function(ev) {
            if ($dateInput.val()) {
                if (datepicker.getDate()) {
                    $element.val(datepicker.getDate().format('YYYY-MM-DD HH:mm:00'));
                }
            } else {
                $element.val('');
            }
        });

        $element
                .hide()
                .after($dateInput)
                ;

        $dateInput.trigger('change');

    });
});
