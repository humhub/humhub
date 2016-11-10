/**
 * Module for creating an manipulating modal dialoges.
 * Normal layout of a dialog:
 * 
 * <div class="modal">
 *     <div class="modal-dialog">
 *         <div class="modal-content">
 *             <div class="modal-header"></div>
 *             <div class="modal-body"></div>
 *             <div class="modal-footer"></div>
 *         </div>
 *     </div>
 * </div>
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.initModule('ui.modal', function (module, require, $) {
    var object = require('util').object;
    var additions = require('ui.additions');
    var config = require('config').module(module);
    var loader = require('ui.loader');
    var client = require('client', true);

    module.initOnPjaxLoad = false;

    //Keeps track of all initialized modals
    var modals = [];


    /**
     * Template for the modal splitted into different parts. Those can be overwritten my changing or overwriting module.template.
     */
    var template = {
        container: '<div class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none; background:rgba(0,0,0,0.1)"><div class="modal-dialog"><div class="modal-content"></div></div></div>',
        header: '<div class="modal-header"><button type="button" class="close" data-modal-close="true" aria-hidden="true">×</button><h4 class="modal-title"></h4></div>',
        body: '<div class="modal-body"></div>',
    };

    var ERROR_DEFAULT_TITLE = 'Error';
    var ERROR_DEFAULT_MESSAGE = 'An unknown error occured!';

    /**
     * The Modal class can be used to create new modals or manipulate existing modals.
     * If the constructor finds an element with the given id we use the existing modal,
     * if the id is not already used, we create a new modal dom element.
     * 
     * @param {string} id - id of the modal
     */
    var Modal = function (id) {
        this.$ = $('#' + id);
        if (!this.$.length) {
            this.createModal(id);
        }
        this.initModal();
        modals.push(this);
    };

    /**
     * Creates a new modal dom skeleton.
     * @param {type} id the modal id
     * @returns {undefined}
     */
    Modal.prototype.createModal = function (id) {
        this.$ = $(module.template.container).attr('id', id);
        $('body').append(this.$);
    };

    /**
     * Initializes default modal events and sets initial data.
     * @returns {undefined}
     */
    Modal.prototype.initModal = function () {
        //Set the loader as default content
        this.reset();
        var that = this;

        //Set default modal manipulation event handlers
        this.getDialog().on('click', '[data-modal-close]', function () {
            that.close();
        }).on('click', '[data-modal-clear-error]', function () {
            that.clearErrorMessage();
        });

        this.$.attr('aria-labelledby', this.getTitleId());

    };

    Modal.prototype.getTitleId = function () {
        return this.$.attr('id') + '-title';
    };

    /**
     * Closes the modal with fade animation and sets the loader content
     * @returns {undefined}
     */
    Modal.prototype.close = function (reset) {
        var that = this;
        this.$.fadeOut('fast', function () {
            that.$.modal('hide');
            if (reset) {
                that.reset();
            }
        });
    };

    /**
     * Sets the loader content and shows the modal
     * @returns {undefined}
     */
    Modal.prototype.loader = function () {
        this.reset();
        this.show();
    };

    /**
     * Sets the default content (a loader animation)
     * @returns {undefined}
     */
    Modal.prototype.reset = function () {
        // Clear old script tags.
        var $content = this.getContent().empty();
        this.$.find('script').remove();
        $content.append('<div class="modal-body" />');
        loader.set(this.getBody());
        this.isFilled = false;
    };

    /**
     * Sets the given content and applies content additions.
     * @param {string|jQuery} content - content to be set
     * @param {function} callback - callback function is called after html was inserted
     * @returns {undefined}
     */
    Modal.prototype.content = function (content, callback) {
        try {
            var that = this;
            this.clearErrorMessage();
            this.getContent().html(content).promise().always(function () {
                that.applyAdditions();
                !callback || callback(this.$);
            });
            this.isFilled = true;
        } catch (err) {
            console.error('Error while setting modal content', err);
            this.setErrorMessage(err.message);
            // We try to apply additions anyway
            that.applyAdditions();
        }
    };

    Modal.prototype.applyAdditions = function () {
        additions.applyTo(this.getContent());
    };

    /**
     * Sets an errormessage and title. This function either creates an standalone
     * error modal with title and message, or adds/replaces a errorboxmessage to
     * already exising and filled modals.
     * @param {type} title
     * @param {type} message
     * @returns {undefined}
     */
    Modal.prototype.error = function (title, message) {

        if (arguments.length === 1 && title) {
            message = (title.getFirstError) ? title.getFirstError() : title;
            title = (title.getErrorTitle) ? title.getErrorTitle() : ERROR_DEFAULT_TITLE;
        }

        title = title || ERROR_DEFAULT_TITLE;
        message = message || ERROR_DEFAULT_MESSAGE;

        //If there is no content yet we create an error only content
        if (!this.isFilled) {
            this.clear();
            this.setTitle(title);
            this.setBody('');
            this.setErrorMessage(message);
            this.show();
        } else {
            //TODO: allow to set errorMessage and title even for inline messages
            this.setErrorMessage(message);
        }
    };

    /**
     * Removes existing error messages
     * @returns {undefined}
     */
    Modal.prototype.clearErrorMessage = function () {
        var modalError = this.getErrorMessage();
        if (modalError.length) {
            modalError.fadeOut('fast', function () {
                modalError.remove();
            });
        }
    };

    /**
     * Adds or replaces an errormessagebox
     * @param {type} message
     * @returns {undefined}
     */
    Modal.prototype.setErrorMessage = function (message) {
        var $errorMessage = this.getErrorMessage();
        if ($errorMessage.length) {
            $errorMessage.css('opacity', 0);
            $errorMessage.text(message);
            $errorMessage.animate({'opacity': 1}, 'fast');
        } else {
            this.getBody().prepend('<div class="modal-error alert alert-danger">' + message + '</div>');
        }
    };

    /**
     * Returns the current errormessagebox
     * @returns {humhub.ui.modal_L18.Modal.prototype@call;getContent@call;find}
     */
    Modal.prototype.getErrorMessage = function () {
        return this.getContent().find('.modal-error');
    };

    /**
     * Shows the modal
     * @returns {undefined}
     */
    Modal.prototype.show = function () {
        if (!this.$.is(':visible')) {
            this.$.modal('show');
        }
    };

    /**
     * Clears the modal content
     * @returns {undefined}
     */
    Modal.prototype.clear = function () {
        this.getContent().empty();
    };

    /**
     * Retrieves the modal content jQuery representation
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getContent = function () {
        //We use the :first selector since jQuery refused to execute javascript if we set content with inline js
        return this.$.find('.modal-content:first');
    };

    /**
     * Retrieves the modal dialog jQuery representation
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getDialog = function () {
        return this.$.find('.modal-dialog');
    };

    /**
     * Returns the modal footer
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getFooter = function () {
        return this.$.find('.modal-footer');
    };

    /**
     * Searches for forms within the modal
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getForm = function () {
        return this.$.find('form');
    };

    /**
     * Adds or replaces a modal-title with close button and a title text.
     * @param {type} title
     * @returns {undefined}
     */
    Modal.prototype.setTitle = function (title) {
        var $header = this.getHeader();
        if (!$header.length) {
            $header = $(module.template.header);
            this.getContent().prepend($header);
        }

        // Set title id for aria-labelledby
        $header.find('.modal-title').attr('id', this.getTitleId()).html(title);
    };

    /**
     * Retrieves the modal-header element
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getHeader = function () {
        return this.$.find('.modal-header');
    };

    /**
     * Adds or replaces the current modal-body
     * @param {type} content
     * @returns {undefined}
     */
    Modal.prototype.setBody = function (content) {
        var $body = this.getBody();
        if (!$body.length) {
            this.getContent().append($(module.template.body));
            $body = this.getBody();
        }
        $body.html(content);
    };

    Modal.prototype.replaceWith = function (content) {
        this.$.empty().append(content);
        this.$.find('input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first().focus();
        this.applyAdditions();
    };

    /**
     * Retrieves the modal-body element
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getBody = function () {
        return this.$.find('.modal-body');
    };

    var ConfirmModal = function (id) {
        Modal.call(this, id);
    };

    object.inherits(ConfirmModal, Modal);

    ConfirmModal.prototype.open = function (cfg) {
        var that = this;
        return new Promise(function (resolve, reject) {
            cfg = cfg || {};

            cfg.confirm = resolve;
            cfg.reject = reject;

            that.clear();
            cfg['header'] = cfg['header'] || config['defaultConfirmHeader'];
            cfg['body'] = cfg['body'] || config['defaultConfirmBody'];
            cfg['confirmText'] = cfg['confirmText'] || config['defaultConfirmText'];
            cfg['cancleText'] = cfg['cancleText'] || config['defaultCancelText'];
            that.setTitle(cfg['header']);
            that.setBody(cfg['body']);
            that.initButtons(cfg);
            that.show();
        });

    };

    ConfirmModal.prototype.clear = function (cfg) {
        this.$.find('[data-modal-confirm]').off('click');
        this.$.find('[data-modal-cancel]').off('click');
    };

    ConfirmModal.prototype.initButtons = function (cfg) {
        //Set button text
        var $cancelButton = this.$.find('[data-modal-cancel]');
        $cancelButton.text(cfg['cancleText']);

        var $confirmButton = this.$.find('[data-modal-confirm]');
        $confirmButton.text(cfg['confirmText']);

        //Init handler
        var that = this;
        if (cfg['confirm']) {
            $confirmButton.one('click', function (evt) {
                that.clear();
                cfg['confirm'](evt);
            });
        }

        if (cfg['cancel']) {
            $cancelButton.one('click', function (evt) {
                that.clear();
                cfg['cancel'](evt);
            });
        }
    };

    var init = function () {
        module.global = new Modal('globalModal');
        module.global.$.on('hidden.bs.modal', function (e) {
            module.global.reset();
        });

        module.globalConfirm = new ConfirmModal('globalModalConfirm');
        module.confirm = function (cfg) {
            return module.globalConfirm.open(cfg);
        };

        _setModalEnforceFocus();
        _setGlobalModalTargetHandler();

        $(document).on('show.bs.modal', '.modal', function (event) {
            $(this).appendTo($('body'));
        });

        $(document).on('shown.bs.modal', '.modal.in', function (event) {
             _setModalsAndBackdropsOrder();
        });

        $(document).on('hidden.bs.modal', '.modal', function (event) {
             _setModalsAndBackdropsOrder();
        });
    };

    var _setModalsAndBackdropsOrder = function () {
        var modalZIndex = 1040;
        $('.modal.in').each(function (index) {
            var $modal = $(this);
            modalZIndex++;
            $modal.css('zIndex', modalZIndex);
            $modal.next('.modal-backdrop.in').addClass('hidden').css('zIndex', modalZIndex - 1);
        });
        $('.modal.in:visible:last').focus().next('.modal-backdrop.in').removeClass('hidden');
    }

    /**
     * To allow other frameworks to overlay focusable nodes over an active modal we have
     * to explicitly allow ith within this overwritten function.
     *
     */
    var _setModalEnforceFocus = function () {
        $.fn.modal.Constructor.prototype.enforceFocus = function () {
            var that = this;
            $(document).on('focusin.modal', function (e) {
                var $target = $(e.target);
                if ($target.hasClass('select2-input') || $target.hasClass('select2-search__field') || $target.hasClass('hexInput')) {
                    return true;
                }

                var $parent = $(e.target.parentNode);
                if ($parent.hasClass('cke_dialog_ui_input_select') || $parent.hasClass('cke_dialog_ui_input_text')) {
                    return true;
                }

                // Allow stacking of modals
                if ($target.closest('.modal.in').length) {
                    return true;
                }

                if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
                    that.$element.focus();
                }
            });
        };
    };

    var _setGlobalModalTargetHandler = function () {

        // unbind all previously-attached events
        $("a[data-target='#globalModal']").off('.humhub:globalModal');

        // deprecated use action handler instead @see get action
        $(document).off('click.humhub:globalModal').on('click.humhub:globalModal', "a[data-target='#globalModal']", function (evt) {
            evt.preventDefault();

            var options = {
                'show': true,
                'backdrop': $(this).data('backdrop'),
                'keyboard': $(this).data('keyboard')
            };

            $("#globalModal").modal(options);

            var target = $(this).attr("href");

            client.html(target).then(function (response) {
                module.global.replaceWith(response.html);
                if (!module.global.$.is(':visible')) {
                    module.global.show();
                }
            }).catch(function (error) {
                module.log.error(error, true);
            });
        });
    };

    var submit = function (evt) {
        evt.$form = evt.$form || evt.$trigger.closest('form');
        client.submit(evt, {'dataType': 'html'}).then(function (response) {
            module.global.replaceWith(response.html);
        }).catch(function (error) {
            module.log.error(error, true);
        });
    };

    var get = function (evt) {
        client.html(evt).then(function (response) {
            module.global.replaceWith(response.html);
        }).catch(function (error) {
            module.log.error(error, true);
        });
    }

    module.export({
        init: init,
        Modal: Modal,
        template: template,
        get: get,
        submit: submit
    });
});