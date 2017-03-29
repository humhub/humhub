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

    var instance;

    var CreateForm = function(node) {
        Widget.call(this, node);
    };

    object.inherits(CreateForm, Widget);

    CreateForm.prototype.init = function() {
        this.$.hide();

        // Hide options by default
        $('.contentForm_options').hide();
        $('#contentFormError').hide();

        this.setDefaultVisibility();

        this.$.fadeIn('fast');

        if(!module.config['disabled']) {
            // Remove info text from the textinput
            $('#contentFormBody').on('click.humhub:content:form dragover.humhub:content:form', function() {
                // Hide options by default
                $('.contentForm_options').fadeIn();
            });
        } else {
            $('#contentFormBody').find('.humhub-ui-richtext').trigger('disable');
        }
    };

    CreateForm.prototype.submit = function(evt) {
        this.$.find("#contentFormError, .preferences, .fileinput-button").hide();
        this.$.find("#contentFormError li").remove();

        var that = this;
        evt.block = 'manual';
        client.submit(evt).then(function(response) {
            that.$.find(".preferences, .fileinput-button").show();
            $('.contentForm_options .preferences, .fileinput-button').show();
            if(!response.errors) {
                event.trigger('humhub:modules:content:newEntry', response.output);
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

        this.resetNotifyUser();
        this.setDefaultVisibility();
        this.resetFilePreview();

        $('#public').attr('checked', false);
        $('#contentFormBody').find('.humhub-ui-richtext').trigger('clear');
    };

    CreateForm.prototype.resetNotifyUser = function() {
        $('#notifyUserContainer').hide();
        Widget.instance('#notifyUserInput').reset();
    };

    CreateForm.prototype.resetFilePreview = function() {
        Widget.instance($('#contentFormFiles_preview')).reset();
    };

    CreateForm.prototype.handleError = function(response) {
        $('#contentFormError').show();
        $.each(response.errors, function(fieldName, errorMessage) {
            // Mark Fields as Error
            var fieldId = 'contentForm_' + fieldName;
            $('#' + fieldId).addClass('error');
            $.each(errorMessage, function(key, msg) {
                $('#contentFormError').append('<li><i class=\"icon-warning-sign\"></i> ' + msg + '</li>');
            });
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
        instance: instance,
        init: init,
        initOnPjaxLoad: true,
        unload: unload
    });
});