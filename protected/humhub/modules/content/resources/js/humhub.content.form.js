/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('content.form', function (module, require, $) {

    var CREATE_FORM_ROOT_SELECTOR = '#contentFormBody';
    var CREATE_FORM_ROOT_SELECTOR_MODAL = CREATE_FORM_ROOT_SELECTOR + 'Modal';

    var object = require('util').object;
    var client = require('client');
    var event = require('event');
    var Widget = require('ui.widget').Widget;
    var loader = require('ui.loader');
    var modal = require('ui.modal');

    var instance;

    var CreateForm = function (node) {
        Widget.call(this, node);
    };

    object.inherits(CreateForm, Widget);

    CreateForm.prototype.init = function () {
        this.isModal = this.$.is(CREATE_FORM_ROOT_SELECTOR_MODAL)

        if (!this.isModal) {
            this.$.hide();
            this.menu = this.$.parent().prev('#contentFormMenu'); // #contentFormMenuModal doesn't exist
            // Hide options by default
            this.$.find('.contentForm_options').hide();
        }

        this.setDefaultVisibility();
        this.$.fadeIn('fast');
        this.showMenu();

        if (!module.config['disabled']) {
            this.$.on('click.humhub:content:form dragover.humhub:content:form', function (evt) {
                // Prevent fading in for topic remove button clicks
                if ($(evt.target).closest('.topic-remove-label').length) {
                    return;
                }

                if (!this.isModal) {
                    this.$.find('.contentForm_options').fadeIn();
                }
            }.bind(this));
        } else {
            this.$.find('.humhub-ui-richtext').trigger('disable');
        }
    };

    CreateForm.prototype.showMenu = function () {
        this.menu.find('li,a').removeClass('active');
        const firstMenuWithForm = this.menu.find('a[data-action-click=loadForm]:eq(0)');
        if (firstMenuWithForm.length) {
            firstMenuWithForm.addClass('active')
                .parent().addClass('active');
        }
        this.menu.fadeIn();
    }

    CreateForm.prototype.submit = function (evt) {
        this.$.find('.preferences, .fileinput-button').hide();
        this.$.find('.help-block-error').html('');
        this.$.find('.has-error').removeClass('has-error');

        var that = this;
        evt.block = 'manual';
        event.trigger('humhub:content:beforeSubmit', this);
        client.submit(evt).then(function (response) {
            that.$.find(".preferences, .fileinput-button").show();
            that.$.find('.contentForm_options .preferences, .fileinput-button').show();
            if (!response.errors) {
                event.trigger('humhub:content:newEntry', response.output, this);
                event.trigger('humhub:content:afterSubmit', response.output, this);
                if ($('#share-intend-modal').length) {
                    $("#globalModal").modal("hide");
                    // If the dashboard stream is not displayed on the current page, redirect to the content container, to make sure the user sees the new content
                    if (response.url && !$('.dashboard-wall-stream').length) {
                        client.pjax.redirect(module.config.redirectToContentContainerUrl.replace('the-content-id', response.data.id));
                    }
                } else {
                    that.resetForm();
                }
            } else {
                that.handleError(response);
            }
        }).catch(function (e) {
            module.log.error(e, true);
        }).finally(function () {
            evt.finish();
        });
    };

    /**
     * Todo: this is post form only, this needs to be added to post module perhaps by calling $form.trigger('humhub:form:clear');
     *
     * As the form for share intend is in a modal, we don't need to reset it
     *
     * @returns {undefined}
     */
    CreateForm.prototype.resetForm = function () {
        // Reset Form (Empty State)
        this.$.find('.contentForm_options').hide();
        var $contentForm = this.$.find('.contentForm');
        $contentForm.filter(':text').val('');
        $contentForm.filter('textarea').val('').trigger('autosize.resize');
        $contentForm.attr('checked', false);

        this.resetSettingInputs();
        this.setDefaultVisibility();
        this.resetFilePreview();
        this.resetFileUpload();
        this.resetState();

        this.$.find('.humhub-ui-richtext').trigger('clear');
    };

    CreateForm.prototype.resetSettingInputs = function () {
        this.$.find('.notifyUserContainer').hide();
        Widget.instance('#notifyUserInput' + (this.isModal ? 'Modal' : '')).reset();
        $('#postTopicContainer' + (this.isModal ? 'Modal' : '')).hide();

        var topicPicker = Widget.instance('#postTopicInput' + (this.isModal ? 'Modal' : ''));
        if (topicPicker) {
            topicPicker.reset();
        }
    };

    CreateForm.prototype.resetFilePreview = function () {
        var preview = Widget.instance($('#contentFormFiles_preview' + (this.isModal ? 'Modal' : '')));
        if (preview) {
            preview.reset();
        }
    };

    CreateForm.prototype.resetFileUpload = function () {
        var upload = Widget.instance($('#contentFormFiles_progress' + (this.isModal ? 'Modal' : '')));
        if (upload) {
            upload.reset();
        }
    };

    CreateForm.prototype.handleError = function (response) {
        var that = this;
        var model = that.$.find('.form-group:first').attr('class').replace(/^.+field-([^-]+).+$/, '$1');
        $.each(response.errors, function (fieldName, errorMessages) {
            var fieldSelector = '.field-' + model + '-' + fieldName;
            var inputSelector = '.field-contentForm_' + fieldName;
            var multiInputSelector = '[name="' + fieldName + '[]"]';
            that.$.find(fieldSelector).addClass('has-error');
            that.$.find(fieldSelector + ', ' + inputSelector + ', ' + inputSelector + '_input')
                .find('.help-block-error:first').html(errorMessages.join('<br>'));
            that.$.find(multiInputSelector).closest('.form-group').addClass('has-error');
        });
    };

    CreateForm.prototype.getForm = function () {
        return this.$.find('form:visible');
    };

    CreateForm.prototype.changeVisibility = function () {
        if (!this.$.find('.contentForm_visibility').prop('checked')) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    };

    CreateForm.prototype.setDefaultVisibility = function () {
        if (module.config['defaultVisibility']) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    };

    CreateForm.prototype.setPublicVisibility = function () {
        this.$.find('.contentForm_visibility').prop("checked", true);
        this.$.find('.contentForm_visibility_entry').html('<i class="fa fa-lock"></i>' + module.text(['makePrivate']));
        this.$.find('.label-public').removeClass('hidden');
    };

    CreateForm.prototype.setPrivateVisibility = function () {
        this.$.find('.contentForm_visibility').prop("checked", false);
        this.$.find('.contentForm_visibility_entry').html('<i class="fa fa-unlock"></i>' + module.text(['makePublic']));
        this.$.find('.label-public').addClass('hidden');
    };

    CreateForm.prototype.notifyUser = function () {
        this.$.find('.notifyUserContainer').show();
        Widget.instance('#notifyUserInput' + (this.isModal ? 'Modal' : '')).focus();
    };

    CreateForm.prototype.setTopics = function () {
        $('#postTopicContainer' + (this.isModal ? 'Modal' : '')).show();

        var topicPicker = Widget.instance('#postTopicInput' + (this.isModal ? 'Modal' : ''));
        if (topicPicker) {
            topicPicker.focus();
        }
    };

    CreateForm.prototype.changeState = function (state, title, buttonTitle) {
        const stateInput = this.$.find('input[name=state]');
        let stateLabel = this.$.find('.label-content-state');
        const button = $('#post_submit_button' + (this.isModal ? '_modal' : ''));

        if (!stateLabel.length) {
            stateLabel = $('<span>').addClass('label label-warning label-content-state');
            this.$.find('.label-container').append(stateLabel);
        }

        if (stateInput.data('initial') === undefined) {
            stateInput.data('initial', {
                state: stateInput.val(),
                buttonTitle: button.html()
            });
        }

        if (typeof (state) === 'object') {
            buttonTitle = state.$target.data('button-title');
            title = state.$target.data('state-title');
            state = state.$target.data('state');
            if (stateInput.val() == state) {
                return this.resetState();
            }
        }

        stateInput.val(state);
        stateLabel.show().html(title);
        button.html(buttonTitle);
        this.$.find('.preferences [data-action-click=notifyUser]').parent().hide();
        this.$.find('.notifyUserContainer').hide();
    }

    CreateForm.prototype.resetState = function () {
        const stateInput = this.$.find('input[name=state]');
        const button = $('#post_submit_button' + (this.isModal ? '_modal' : ''));
        const initial = stateInput.data('initial');
        if (initial !== undefined) {
            stateInput.val(initial.state);
            if (loader.is(button)) {
                button.data('htmlOld', initial.buttonTitle).removeAttr('style');
                loader.reset(button);
            } else {
                button.html(initial.buttonTitle);
            }
        }
        this.$.find('input[name^=scheduled]').remove();
        this.$.find('.label-content-state').hide();
        this.$.find('.preferences [data-action-click=notifyUser]').parent().show();
        const notifyUserContainer = this.$.find('.notifyUserContainer');
        if (notifyUserContainer.find('ul .select2-selection__clear').length) {
            notifyUserContainer.show();
        }
    }

    /**
     * Schedule is not available for share intend because it is already in a modal
     */
    CreateForm.prototype.scheduleOptions = function (evt) {
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
                    that.changeState(
                        modalGlobal.find('input[name=state]').val(),
                        modalGlobal.find('input[name=stateTitle]').val(),
                        modalGlobal.find('input[name=buttonTitle]').val());
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

    /**
     * Schedule is not available for share intend because it is already in a modal
     */
    CreateForm.prototype.setScheduleOption = function (name, value) {
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

    /**
     * Schedule is not available for share intend because it is already in a modal
     */
    CreateForm.prototype.resetScheduleOption = function (name) {
        this.setScheduleOption(name);
    }

    /**
     * CreateFormMenu is not available for share intend
     */
    const CreateFormMenu = Widget.extend();

    CreateFormMenu.prototype.init = function () {
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

        client.get(evt).then(function (response) {
            that.formPanel.replaceWith(response.html);
            that.formPanel = that.$.parent().find('.panel');
            that.formPanel.find('[data-action-component], [data-ui-widget]').each(function () {
                Widget.instance($(this));
            });
            that.formPanel.find('input[type=text], textarea, .ProseMirror').eq(0).trigger('click').focus();
        }).catch(function (e) {
            module.log.error(e, true);
            loader.reset(that.formPanel);
        });
    }

    var init = function () {
        var $root = $(CREATE_FORM_ROOT_SELECTOR);
        if ($root.length) {
            instance = Widget.instance($root);
        }
    };

    var initModal = function () {
        var $rootModal = $(CREATE_FORM_ROOT_SELECTOR_MODAL);
        if ($rootModal.length) {
            instance = Widget.instance($rootModal);
        }
    };

    var unload = function () {
        instance = undefined;
    }

    module.export({
        CreateForm: CreateForm,
        CreateFormMenu: CreateFormMenu,
        instance: instance,
        init: init,
        initModal: initModal,
        initOnPjaxLoad: true,
        unload: unload
    });
});
