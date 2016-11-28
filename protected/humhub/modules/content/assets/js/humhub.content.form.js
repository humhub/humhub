/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('content.form', function (module, require, $) {

    var CREATE_FORM_ROOT_SELECTOR = '#contentFormBody';

    var util = require('util');
    var client = require('client');

    var config = require('config').module(module);
    var event = require('event');
    
    var instance;

    var CreateForm = function () {
        this.$ = $(CREATE_FORM_ROOT_SELECTOR);
    };
    
    CreateForm.prototype.init = function () {
        this.$.hide();
        
        // Hide options by default
        $('.contentForm_options').hide();
        $('#contentFormError').hide();
        // Remove info text from the textinput
        $('#contentFormBody').click(function () {
            // Hide options by default
            $('.contentForm_options').fadeIn();
        });

        this.setDefaultVisibility();
        
        this.$.fadeIn('fast');
    }

    CreateForm.prototype.actions = function () {
        return ['submit', 'notifyUser', 'changeVisibility'];
    };

    CreateForm.prototype.submit = function (evt) {
        this.$.find("#contentFormError, .preferences, .fileinput-button").fadeOut();
        this.$.find("#contentFormError li").remove();
        
        var that = this;
        client.submit(evt).then(function (response) {
            if (!response.errors) {
                event.trigger('humhub:modules:content:newEntry', response.output);
                that.resetForm();
            } else {
                that.handleError(response);
            }
            that.$.find(".preferences, .fileinput-button").show();
            $('.contentForm_options .preferences, .fileinput-button').show();
        });
    };

    /**
     * Todo: this is post form only, this needs to be added to post module perhaps by calling $form.trigger('humhub:form:clear');
     * @returns {undefined}
     */
    CreateForm.prototype.resetForm = function () {
        // Reset Form (Empty State)
        $('.contentForm_options').hide();
        $('.contentForm').filter(':text').val('');
        $('.contentForm').filter('textarea').val('').trigger('autosize.resize');
        $('.contentForm').attr('checked', false);
        $('.userInput').remove(); // used by UserPickerWidget
        $('#notifyUserContainer').hide();
        $('#notifyUserInput').val('');

        this.setDefaultVisibility();

        $('#contentFrom_files').val('');
        $('#public').attr('checked', false);
        $('#contentForm_message_contenteditable').addClass('atwho-placeholder');

        $('#contentFormBody').find('.atwho-input').trigger('clear');

        // Notify FileUploadButtonWidget to clear (by providing uploaderId)
        // TODO: use api
        resetUploader('contentFormFiles');
    };

    CreateForm.prototype.handleError = function (response) {
        $('#contentFormError').show();
        $.each(response.errors, function (fieldName, errorMessage) {
            // Mark Fields as Error
            var fieldId = 'contentForm_' + fieldName;
            $('#' + fieldId).addClass('error');
            $.each(errorMessage, function (key, msg) {
                $('#contentFormError').append('<li><i class=\"icon-warning-sign\"></i> ' + msg + '</li>');
            });
        });
    };

    CreateForm.prototype.getForm = function () {
        return this.$.find('form:visible');
    };
    
    CreateForm.prototype.changeVisibility = function() {
        if (!$('#contentForm_visibility').prop('checked')) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    };
    
    CreateForm.prototype.setDefaultVisibility = function() {
        if (config['defaultVisibility']) {
            this.setPublicVisibility();
        } else {
            this.setPrivateVisibility();
        }
    }
    
    CreateForm.prototype.setPublicVisibility = function() {
        $('#contentForm_visibility').prop("checked", true);
        $('#contentForm_visibility_entry').html('<i class="fa fa-lock"></i>'+config['text']['makePrivate']);
        $('.label-public').removeClass('hidden');
    };
    
    CreateForm.prototype.setPrivateVisibility = function() {
        $('#contentForm_visibility').prop("checked", false);
        $('#contentForm_visibility_entry').html('<i class="fa fa-unlock"></i>'+config['text']['makePublic']);
        $('.label-public').addClass('hidden');
    };

    CreateForm.prototype.notifyUser = function() {
        $('#notifyUserContainer').show();
        $('#notifyUserInput').data('picker').focus()
    };

    var init = function () {
        instance = new CreateForm();
        instance.init();
    };

    module.export({
        CreateForm: CreateForm,
        instance: instance,
        init: init
    });
});