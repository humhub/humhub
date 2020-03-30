humhub.module('ui.form.elements', function (module, require, $) {
    var additions = require('ui.additions');

    var init = function () {
        additions.register('form_elements', ':checkbox, :radio', function ($match) {
            $match.each(function () {
                var $this = $(this);
                if ($this.is(':checkbox')) {
                    module.initCheckbox($this);
                } else if($this.is(':radio')) {
                    module.initRadio($this);
                }
            });
        });

        additions.register('password', '[type="password"]', function ($match) {

            $match.each(function () {
                var $input = $(this);
                var $formGroup = $input.parent('.form-group');
                var invisibleTop = 0;

                /**
                 * We can't calculate top if input is invisible,
                 * Note, this may not work for more complex cases.
                 */
                if(!$input.is(':visible')) {
                    if($input.siblings('label').length) {
                        invisibleTop = '23px';
                    }
                }

                if($formGroup.length) {
                    $formGroup.css('position', 'relative');
                    var $pwShow = $('<div class="humhub-pw-show"><i class="fa fa-eye"></i></div>').on('click', function() {
                        var $icon = $(this).find('i');
                        if ($input.attr('type') ==='password') {
                            $input.attr('type', 'input');
                            $icon.addClass('fa-eye-slash').removeClass('fa-eye');
                        } else {
                            $input.attr('type', 'password');
                            $icon.addClass('fa-eye').removeClass('fa-eye-slash');
                        }
                    }).css({
                        'position' : 'absolute',
                        'right' : '2px',
                        'padding': '4px',
                        'font-size': '19px',
                        'cursor': 'pointer',
                        'top': !$input.is(':visible') ? invisibleTop :  $input.position().top
                    });

                    $formGroup.prepend($pwShow);
                }
            });
        });
    };

    var initCheckbox = function ($input) {

        if ($input.data('form_element') || $input.hasClass('hidden')) {
            return;
        }

        // Check if the standard bootstrap container <div> exists
        if ($input.parent().parent().attr('class') === "checkbox") {
            $input.parent().parent().addClass('regular-checkbox-container')
                    .append('<div class="regular-checkbox-clear"></div>');
        }

        _checkInputLabel($input);

        // Create new checkbox
        var $checkbox = $('<div class="regular-checkbox-box"></div>').attr('style', $input.attr('style'));

        if ($input.is(':disabled')) {
            $checkbox.addClass('disabled');
        }

        // add new checkbox element
        $input.parent().append($checkbox);

        // add new class to checkbox
        $input.addClass('regular-checkbox').data('form_element', $checkbox);
    };

    var initRadio = function ($input) {
        if ($input.data('form_element')) {
            return;
        }

        // Check if the standard bootstrap container <div> exists
        if ($input.parent().parent().attr('class') === "radio") {
            $input.parent().parent().addClass('regular-radio-container');
        }

        _checkInputLabel($input);

        // Create new checkbox
        var $radio = $('<div class="regular-radio-button"></div>');

        if ($input.is(':disabled')) {
            $radio.addClass('disabled');
        }

        // add new radio element
        $input.parent().append($radio);

        // add new class to checkbox
        $input.addClass('regular-radio').data('form_element', $radio);
    };

    /**
     * Checks the label style of $input.
     * If the $input is not wrapped in a label we wrap it around a new label and use the old label text if given.
     *
     * @param {type} $input
     * @returns {undefined}
     */
    var _checkInputLabel = function ($input) {
        if (!$input.parent().is('label')) {
            var $parent = $input.parent();

            var $newLabel = $('<label>');

            // check for old label
            var $oldLabel = $('label[for="' + $input.attr('id') + '"]');
            if ($oldLabel.length) {
                $newLabel.html($oldLabel.html());
                $oldLabel.remove();
            }

            $parent.append($newLabel.append($input));
        }
    };

    var toggleTimeZoneInput = function(evt) {
        evt.$trigger.siblings('.timeZoneInputContainer:first').fadeToggle('fast');
    };

    var timeZoneSelected = function(evt) {
        var $toggleButton = evt.$trigger.parent().siblings('.timeZoneToggle:first');
        $toggleButton.text(evt.$trigger.find('option:selected').text());
        evt.$trigger.parent().hide();
    };



    module.export({
        init: init,
        sortOrder: 100,
        initCheckbox: initCheckbox,
        initRadio: initRadio,
        toggleTimeZoneInput: toggleTimeZoneInput,
        timeZoneSelected: timeZoneSelected
    });
});
