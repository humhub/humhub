humhub.module('ui.form.elements', function (module, require, $) {
    var additions = require('ui.additions');

    var init = function () {
        additions.register('password', '[type="password"]', function ($match) {

            $match.each(function () {
                var $input = $(this);
                var $formGroup = $input.parent('.mb-3');
                var invisibleTop = 0;

                var timeout = $formGroup.closest('.animated').length ? 800 : 100;

                setTimeout(function() {
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

                        $formGroup.prepend($pwShow.hide());
                        $pwShow.fadeIn('fast');
                    }
                }, timeout);

            });
        });

        additions.register('radio-pills', '.radio-pills [type=radio]', function ($match) {
            $match.on('change', function () {
                $(this).closest('.radio-pills').find('.radio.active').removeClass('active');
                $(this).closest('.radio').addClass('active');
            });
        });
    };

    var toggleTimeZoneInput = function(evt) {
        evt.$trigger.siblings('.timeZoneInputContainer:first').fadeToggle('fast');
    };

    var timeZoneSelected = function(evt) {
        var $toggleButton = evt.$trigger.parent().siblings('.timeZoneToggle:first');
        $toggleButton.text(evt.$trigger.find('option:selected').text());
        evt.$trigger.parent().hide();
    };

    var object = require('util').object;
    var Widget = require('ui.widget').Widget;

    var FormFieldsCollapsible = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(FormFieldsCollapsible, Widget);

    FormFieldsCollapsible.prototype.init = function () {
        if (this.$.find('.error, .is-invalid').length > 0) {
            this.$.find('.form-collapsible-fields-label').removeClass('collapsed');
            this.$.find('fieldset').addClass('show');
        }
    };

    const validate = {
        trim: function($form, attribute, options, value) {
            var $input = $form.find(attribute.input);
            if ($input.is(':checkbox, :radio')) {
                return value;
            }

            value = $input.val();
            if (!options.skipOnEmpty || !yii.validation.isEmpty(value)) {
                value = value.replace(/^[\p{Z}\s]+|[\p{Z}\s]+$/gu, ' ').trim();
                $input.val(value);
            }

            return value;
        },
        required: function(value, messages, options) {
            if ((typeof value == 'string' || value instanceof String) && !value.replace(/[\p{Z}\s]+/gu, '').length) {
                value = '';
            }

            return yii.validation.required(value, messages, options)
        }
    }

    module.export({
        init: init,
        validate: validate,
        sortOrder: 100,
        toggleTimeZoneInput: toggleTimeZoneInput,
        timeZoneSelected: timeZoneSelected,
        FormFieldsCollapsible: FormFieldsCollapsible
    });
});
