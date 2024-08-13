humhub.module('ui.form.elements', function (module, require, $) {
    var additions = require('ui.additions');

    var init = function () {
        additions.register('password', '[type="password"]', function ($match) {

            $match.each(function () {
                var $input = $(this);
                var $formGroup = $input.parent('.form-group');
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

    FormFieldsCollapsible.component = 'humhub-form-field-collapsible';
    FormFieldsCollapsible.prototype.validate = function () {
        return this.$.is('div');
    };

    FormFieldsCollapsible.prototype.clickCollab = function (evt) {
        if (this.$.find('fieldset').is(":visible")) {
            this.hide();
        } else {
            this.show();
        }
    };

    FormFieldsCollapsible.prototype.init = function () {
        if (!this.$.attr('tabindex')) {
            this.$.attr('tabindex', 0);
        }

        var that = this;
        this.$.on('keyup', function (e) {
            if(e.which === 9 || !that.$.is(":focus")){
                return;
            }

            that.clickCollab();
        });

        if (this.$.find('.error, .has-error').length > 0) {
            this.show();
        }
    };

    FormFieldsCollapsible.prototype.hide = function () {
        this.$.find('fieldset').attr("aria-hidden","true");
        this.$.find('fieldset').attr("aria-expanded","false");
        this.$.addClass('closed');
        this.$.removeClass('opened');
    };

    FormFieldsCollapsible.prototype.show = function () {
        this.$.find('fieldset').attr("aria-hidden","false");
        this.$.find('fieldset').attr("aria-expanded","true");
        this.$.addClass('opened');
        this.$.removeClass('closed');
    };

    module.export({
        init: init,
        sortOrder: 100,
        toggleTimeZoneInput: toggleTimeZoneInput,
        timeZoneSelected: timeZoneSelected,
        FormFieldsCollapsible: FormFieldsCollapsible
    });
});
