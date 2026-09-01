humhub.module('ui.form.elements', function (module, require, $) {
    var additions = require('ui.additions');

    var init = function () {
        additions.register('password', '[type="password"]', function ($match) {

            $match.each(function () {
                var $input = $(this);
                var $formGroup = $input.parent('.mb-3');

                if (!$formGroup.length) {
                    return;
                }

                $formGroup.css('position', 'relative');

                // Plain <div>, so it needs tabindex/role/keydown wiring to be
                // reachable and operable via keyboard (Tab + Enter/Space).
                var $pwShow = $('<div class="humhub-pw-show" tabindex="0" role="button" aria-pressed="false"><i class="fa fa-eye"></i></div>');

                var setPasswordVisible = function (visible) {
                    var $icon = $pwShow.find('i');
                    $input.attr('type', visible ? 'input' : 'password');
                    $icon.toggleClass('fa-eye-slash', visible).toggleClass('fa-eye', !visible);
                    $pwShow.attr({
                        'aria-pressed': visible ? 'true' : 'false',
                        'aria-label': visible ? module.text('hidePassword') : module.text('showPassword')
                    });
                };

                setPasswordVisible(false);

                $pwShow.on('click', function () {
                    setPasswordVisible($input.attr('type') === 'password');
                }).on('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setPasswordVisible($input.attr('type') === 'password');
                    }
                }).css({
                    'position' : 'absolute',
                    'right' : '0',
                    'padding': '4px 6px 3px',
                    'font-size': '19px',
                    'cursor': 'pointer'
                });

                // Insert right after the input so Tab reaches
                // the input first, then the show/hide icon.
                $input.after($pwShow.hide());

                /**
                 * The input can still be hidden here (e.g. this form is inside a
                 * modal that's mid fade-in), so $input.position().top would be
                 * wrong. Guessing a fixed delay before reading it raced with
                 * whatever animation was showing the field, so the icon ended up
                 * at a different, sometimes-wrong offset on every open. Instead,
                 * poll until the input is actually visible and only then read its
                 * real position.
                 */
                var attempts = 0;
                var maxAttempts = 40; // ~2s at 50ms interval

                var alignPwShow = function () {
                    var visible = $input.is(':visible');

                    // Stop once the input is visible (real position), or once
                    // we've waited long enough and give up with a best-effort
                    // guess instead (e.g. a field that's never actually shown),
                    // so the icon isn't left hidden forever.
                    if (visible || ++attempts >= maxAttempts) {
                        $pwShow.css('top', visible
                            ? $input.position().top
                            : ($input.siblings('label').length ? '23px' : 0)
                        ).fadeIn('fast');
                        return;
                    }

                    setTimeout(alignPwShow, 50);
                };

                alignPwShow();
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
        this.$.find('[data-bs-toggle=collapse]').on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });

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
