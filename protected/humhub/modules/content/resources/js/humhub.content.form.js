/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('content.form', function(module, require, $) {

    var CREATE_FORM_ROOT_SELECTOR = '#contentFormBody';

    var object = require('util').object;
    var client = require('client');
    var event = require('event');
    var Widget = require('ui.widget').Widget;
    var loader = require('ui.loader');
    var modal = require('ui.modal');

    var instance;

    var CreateForm = function(node) {
        Widget.call(this, node);
    };

    object.inherits(CreateForm, Widget);

    CreateForm.prototype.init = function() {
        this.$.hide();
        this.menu = this.$.parent().prev('#contentFormMenu');
        // Hide options by default
        $('.contentForm_options').hide();

        this.setDefaultVisibility();
        this.$.fadeIn('fast');
        this.showMenu();

        if(!module.config['disabled']) {
            $('#contentFormBody').on('click.humhub:content:form dragover.humhub:content:form', function(evt) {
                // Prevent fading in for topic remove button clicks
                if($(evt.target).closest('.topic-remove-label').length) {
                    return;
                }

                $('.contentForm_options').fadeIn();
            });
        } else {
            $('#contentFormBody').find('.humhub-ui-richtext').trigger('disable');
        }
    };

    CreateForm.prototype.showMenu = function() {
        this.menu.find('li,a').removeClass('active');
        const firstMenuWithForm = this.menu.find('a[data-action-click=loadForm]:eq(0)');
        if (firstMenuWithForm.length) {
            firstMenuWithForm.addClass('active')
                .parent().addClass('active');
        }
        this.menu.fadeIn();
    }

    CreateForm.prototype.submit = function(evt) {
        this.$.find('.preferences, .fileinput-button').hide();
        this.$.find('.help-block-error').html('');
        this.$.find('.has-error').removeClass('has-error');

        var that = this;
        evt.block = 'manual';
        event.trigger('humhub:content:beforeSubmit', this);
        client.submit(evt).then(function(response) {
            that.$.find(".preferences, .fileinput-button").show();
            $('.contentForm_options .preferences, .fileinput-button').show();
            if(!response.errors) {
                event.trigger('humhub:content:newEntry', response.output, this);
                event.trigger('humhub:content:afterSubmit', response.output, this);
                that.resetForm();
            } else {
                that.handleError(response);
            }
        }).catch(function(e) {
            module.log.error(e, true);
        }).finally(function() {
            evt.finish();
        });
    };

    /**
     * Todo: this is post form only, this needs to be added to post module perhaps by calling $form.trigger('humhub:form:clear');
     * @returns {undefined}
     */
    CreateForm.prototype.resetForm = function() {
        // Reset Form (Empty State)
        $('.contentForm_options').hide();
        var $contentForm = $('.contentForm');
        $contentForm.filter(':text').val('');
        $contentForm.filter('textarea').val('').trigger('autosize.resize');
        $contentForm.attr('checked', false);

        this.resetSettingInputs();
        this.setDefaultVisibility();
        this.resetFilePreview();
        this.resetFileUpload();
        this.resetState();

        $('#public').attr('checked', false);
        $('#contentFormBody').find('.humhub-ui-richtext').trigger('clear');
    };

    CreateForm.prototype.resetSettingInputs = function() {
        $('#notifyUserContainer').hide();
        Widget.instance('#notifyUserInput').reset();
        $('#postTopicContainer').hide();

        var topicPicker = Widget.instance('#postTopicInput');
        if(topicPicker) {
            topicPicker.reset();
        }
    };

    CreateForm.prototype.resetFilePreview = function() {
        var preview = Widget.instance($('#contentFormFiles_preview'));
        if(preview) {
            preview.reset();
        }
    };

    CreateForm.prototype.resetFileUpload = function() {
        var upload = Widget.instance($('#contentForm_message-file-upload'));
        if(upload) {
            upload.reset();
        }
    };

    CreateForm.prototype.handleError = function(response) {
        var that = this;
        var model = that.$.find('.form-group:first').attr('class').replace(/^.+field-([^-]+).+$/, '$1');
        $.each(response.errors, function(fieldName, errorMessages) {
            var fieldSelector = '.field-' + model + '-' + fieldName;
            var inputSelector = '.field-contentForm_' + fieldName;
            var multiInputSelector = '[name="' + fieldName + '[]"]';
            that.$.find(fieldSelector).addClass('has-error');
            that.$.find(fieldSelector + ', ' + inputSelector + ', ' + inputSelector + '_input')
                .find('.help-block-error:first').html(errorMessages.join('<br>'));
            that.$.find(multiInputSelector).closest('.form-group').addClass('has-error');
        });
    };

    CreateForm.prototype.getForm = function() {
        return this.$.find('form:visible');
    };

    CreateForm.prototype.changeVisibility = function() {
        if(!$('#contentForm_visibility').prop('checked')) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    };

    CreateForm.prototype.setDefaultVisibility = function() {
        if(module.config['defaultVisibility']) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    };

    CreateForm.prototype.setPublicVisibility = function() {
        $('#contentForm_visibility').prop("checked", true);
        $('#contentForm_visibility_entry').html('<i class="fa fa-lock"></i>' + module.text(['makePrivate']));
        $('.label-public').removeClass('hidden');
    };

    CreateForm.prototype.setPrivateVisibility = function() {
        $('#contentForm_visibility').prop("checked", false);
        $('#contentForm_visibility_entry').html('<i class="fa fa-unlock"></i>' + module.text(['makePublic']));
        $('.label-public').addClass('hidden');
    };

    CreateForm.prototype.notifyUser = function() {
        $('#notifyUserContainer').show();
        Widget.instance('#notifyUserInput').focus();
    };

    CreateForm.prototype.setTopics = function() {
        $('#postTopicContainer').show();

        var topicPicker = Widget.instance('#postTopicInput');
        if(topicPicker) {
            topicPicker.focus();
        }
    };

    CreateForm.prototype.changeState = function(state, title) {
        const stateInput = this.$.find('input[name=state]');
        let stateLabel = this.$.find('.label-content-state');

        if (!stateLabel.length) {
            stateLabel = $('<span>').addClass('label label-warning label-content-state');
            this.$.find('.label-container').append(stateLabel);
        }

        if (stateInput.data('initial') === undefined) {
            stateInput.data('initial', stateInput.val());
        }

        if (typeof(state) === 'object') {
            title = state.$target.data('state-title');
            state = state.$target.data('state');
            if (stateInput.val() == state) {
                return this.resetState();
            }
        }

        stateInput.val(state);
        stateLabel.show().html(title);
    }

    CreateForm.prototype.resetState = function() {
        const stateInput = this.$.find('input[name=state]');
        if (stateInput.data('initial') !== undefined) {
            stateInput.val(stateInput.data('initial'));
        }
        this.$.find('input[name^=scheduled]').remove();
        this.$.find('.label-content-state').hide();
    }

    CreateForm.prototype.scheduleOptions = function(evt) {
        const that = this;
        const modalGlobal = modal.global.$;
        const scheduledDate = that.$.find('input[name=scheduledDate]');
        const data = {};

        if (scheduledDate.length) {
            data.ScheduleOptionsForm = {
                enabled: 1,
                date: scheduledDate.val()
            };
        }

        modal.post(evt, {data}).then(function () {
            modalGlobal.one('submitted', function () {
                if (modalGlobal.find('.has-error').length) {
                    return;
                }

                if (modalGlobal.find('#scheduleoptionsform-enabled').is(':checked')) {
                    that.changeState(modalGlobal.find('input[name=state]').val(), modalGlobal.find('input[name=stateTitle]').val());
                    that.setScheduleOption('scheduledDate', modalGlobal.find('input[name=scheduledDate]').val());
                } else {
                    that.resetState();
                    that.resetScheduleOption('scheduledDate');
                }

                modal.global.close(true);
            });
        }).catch(function (e) {
            module.log.error(e, true);
        });
    }

    CreateForm.prototype.setScheduleOption = function(name, value) {
        let input = this.$.find('input[name=' + name + ']');

        if (value === undefined) {
            input.remove();
            return;
        }

        if (!input.length) {
            input = $('<input name="' + name + '" type="hidden">');
            this.$.find('input[name=state]').after(input);
        }
        input.val(value);
    }

    CreateForm.prototype.resetScheduleOption = function(name) {
        this.setScheduleOption(name);
    }

    const CreateFormMenu = Widget.extend();

    CreateFormMenu.prototype.init = function() {
        this.topMenu = this.$.find('ul.nav');
        this.subMenu = this.$.find('li.content-create-menu-more');
        this.formPanel = this.$.parent().find('.panel');
        if (!this.formPanel.find('form').length) {
            this.$.fadeIn();
            this.$.addClass('menu-without-form');
        }
    }

    CreateFormMenu.prototype.activateMenu = function (evt) {
        this.topMenu.find('li,a').removeClass('active');
        evt.$trigger.addClass('active').find('a').addClass('active');

        if (evt.$trigger.closest('ul.dropdown-menu').length) {
            // Move item from sub menu to top menu
            this.subMenu.find('ul').prepend(this.subMenu.prev());
            this.subMenu.before(evt.$trigger.parent());
        }
    }

    CreateFormMenu.prototype.loadForm = function (evt) {
        const that = this;

        loader.set(that.formPanel);
        that.activateMenu(evt);

        client.get(evt).then(function(response) {
            that.formPanel.replaceWith(response.html);
            that.formPanel = that.$.parent().find('.panel');
            that.formPanel.find('[data-action-component], [data-ui-widget]').each(function () {
                Widget.instance($(this));
            });
            that.formPanel.find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();
        }).catch(function(e) {
            module.log.error(e, true);
            loader.reset(that.formPanel);
        });
    }

    var init = function() {
        var $root = $(CREATE_FORM_ROOT_SELECTOR);
        if($root.length) {
            instance = Widget.instance($root);
        }
    };

    var unload = function() {
        instance = undefined;
    }

    module.export({
        CreateForm: CreateForm,
        CreateFormMenu: CreateFormMenu,
        instance: instance,
        init: init,
        initOnPjaxLoad: true,
        unload: unload
    });
});
