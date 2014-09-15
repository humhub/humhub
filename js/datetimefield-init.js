$(document).ready(function() {

    $('.hhtml-datetime-field').each(function(index, element) {
        
        var $element = $(element)
                , dateTimepickerDefaultOptions = {
                    pickDate: true,
                    pickTime: false,
                    useMinutes: true,
                    useSeconds: false,
                    showToday: true,
                    language: localeId.replace("\_", "\-"),
                    use24hours: true,
                    sideBySide: false,
                    // Initally use DB Timestamp Format
                    // After that we switch to locale format
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
            // Switch to format given by locale
            localeData = moment().localeData();
            datepicker.format = (datepicker.options.pickDate ? localeData.longDateFormat('L') : '');
            if (datepicker.options.pickDate && datepicker.options.pickTime) {
                datepicker.format += ' ';
            }
            datepicker.format += (datepicker.options.pickTime ? localeData.longDateFormat('LT') : '');
            if (datepicker.options.useSeconds) {
                if (localeData.longDateFormat('LT').indexOf(' A') !== -1) {
                    datepicker.format = datepicker.format.split(' A')[0] + ':ss A';
                }
                else {
                    datepicker.format += ':ss';
                }
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
