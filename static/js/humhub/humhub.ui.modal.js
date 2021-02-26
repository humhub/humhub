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
humhub.module('ui.modal', function (module, require, $) {
    var util = require('util');
    var object = util.object;
    var additions = require('ui.additions');
    var loader = require('ui.loader');
    var client = require('client', true);
    var Widget = require('ui.widget').Widget;

    //Keeps track of all initialized modals
    var modals = {};

    var ERROR_DEFAULT_TITLE = 'Error';
    var ERROR_DEFAULT_MESSAGE = 'An unknown error occured!';

    /**
     * The Modal class can be used to create new modals or manipulate existing modals.
     * If the constructor finds an element with the given id we use the existing modal,
     * if the id is not already used, we create a new modal dom element.
     *
     * @param {string} id - id of the modal
     */
    var Modal = function (node, options) {
        if (!$(node).length) {
            node = this.createModal(node);
        }
        Widget.call(this, node, options);
    };

    object.inherits(Modal, Widget);

    Modal.component = 'humhub-ui-modal';

    Modal.prototype.init = function () {
        this.initModal(this.options);
        modals[this.$.attr('id')] = this;
    };

    /**
     * Template for the modal splitted into different parts. Those can be overwritten my changing or overwriting module.template.
     */
    Modal.template = {
        container: '<div class="modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none; background:rgba(0,0,0,0.1)"><div class="modal-dialog"><div class="modal-content"></div></div></div>',
        header: '<div class="modal-header"><button type="button" class="close" data-modal-close="true" aria-hidden="true">×</button><h4 class="modal-title"></h4></div>',
        body: '<div class="modal-body"></div>',
        footer: '<div class="modal-footer"></div>',
    };

    /**
     * Creates a new modal dom skeleton.
     * @param {type} id the modal id
     * @returns {undefined}
     */
    Modal.prototype.createModal = function (id) {
        var modal = $(this.getTemplate('container')).attr('id', id);
        $('body').append(modal);
        return modal;
    };

    Modal.prototype.getTemplate = function (id) {
        return Modal.template[id];
    };

    /**
     * Initializes default modal events and sets initial data.
     * @returns {undefined}
     */
    Modal.prototype.initModal = function (options) {
        var that = this;

        //Set default modal manipulation event handlers
        this.$.off('click.modal').on('click.modal', '[data-modal-close]', function () {
            that.close();
        }).on('click', '[data-modal-clear-error]', function () {
            that.clearErrorMessage();
        });

        this.set(options);
    };

    Modal.prototype.checkAriaLabel = function () {
        var $title = this.$.find('.modal-title');
        if($title.length) {
            $title.attr('id', this.getTitleId());
            this.$.attr('aria-labelledby', this.getTitleId());
        } else {
            this.$.removeAttr('aria-labelledby');
        }
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

        this.getDialog().removeClass('modal-dialog-large modal-dialog-normal modal-dialog-small modal-dialog-extra-small modal-dialog-medium');

        //reset listeners:
        this.resetListener();
    };

    /**
     * Resets some listeners of this modal isntance.
     * @returns {undefined}
     */
    Modal.prototype.resetListener = function () {
        this.$.off('submitted');
    };

    /**
     * Sets the given content and applies content additions.
     * @param {string|jQuery} content - content to be set
     * @param {function} callback - callback function is called after html was inserted
     * @returns {undefined}
     */
    Modal.prototype.setContent = function (content, callback) {
        var that = this;
        return new Promise(function (resolve, reject) {
            // TODO: assure content order header/content/footer
            try {
                that.clearErrorMessage();
                that.getContent().html(content).promise().always(function () {
                    that.applyAdditions();
                });
                that.isFilled = true;
                resolve(that);
            } catch (err) {
                that.setErrorMessage(err.message);
                // We try to apply additions anyway
                that.applyAdditions();
                reject(err);
            }
        });
    };

    Modal.prototype.applyAdditions = function () {
        additions.applyTo(this.getContent());
    };

    Modal.prototype.load = function (url, cfg, originalEvent) {
        var that = this;
        cfg = setDefaultRequestData(cfg);

        return new Promise(function (resolve, reject) {
            if (!that.isVisible()) {
                that.loader();
            }
            client.get(url, cfg, originalEvent).then(function (response) {
                that.setDialog(response);
                resolve(response);
                that.focus();
            }).catch(reject);
        });
    };

    Modal.prototype.post = function (url, cfg, originalEvent) {
        var that = this;
        cfg = setDefaultRequestData(cfg);

        return new Promise(function (resolve, reject) {
            if (!that.isVisible()) {
                that.loader();
            }
            client.post(url, cfg, originalEvent).then(function (response) {
                that.setDialog(response);
                resolve(response);
            }).catch(reject);
        });
    };

    var setDefaultRequestData = function(cfg) {
        cfg = cfg || {};
        cfg.data = cfg.data || {};
        cfg.viewContext = cfg.viewContext || 'modal';
        return cfg;
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
            this.setHeader(title);
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
            if (!this.$.data('bs.modal')) {
                this.$.modal(this.options);
            } else {
                this.set(this.options);
                this.$.modal('show');
            }
            this.focus();
        }

        this.getDialog().show();
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
    Modal.prototype.setHeader = function (title) {
        var $header = this.getHeader();
        if (!$header.length) {
            $header = $(this.getTemplate('header'));
            this.getContent().prepend($header);
        }

        // Set title id for aria-labelledby
        $header.find('.modal-title').attr('id', this.getTitleId()).html(title);
    };

    Modal.prototype.setFooter = function (footer) {
        var $footer = this.getFooter();
        if (!$footer.length) {
            $footer = $(this.getTemplate('footer'));
            this.getContent().append($footer);
        }

        $footer.html(footer);
    };

    Modal.prototype.set = function (options) {
        this.options = options;

        if (this.options.header) {
            this.setHeader(this.options.header);
        }

        if (this.options.body) {
            this.setBody(this.options.body);
        }

        if (this.options.content) {
            this.setContent(this.options.content);
        }

        if (this.options.footer) {
            this.setFooter(this.options.footer);
        }

        if(this.options.size) {
            this.getDialog().addClass('modal-dialog-'+this.options.size);
        }

        this.options.backdrop = object.defaultValue(options.backdrop, 'static');
        this.options.keyboard = object.defaultValue(options.keyboard, false);

        if (this.$.data('bs.modal')) {
            this.$.data('bs.modal').options = this.options;
        }

        return this;
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
            this.getContent().append($(this.getTemplate('body')));
            $body = this.getBody();
        }
        $body.html(content);
    };

    Modal.prototype.setDialog = function (content) {
        if (content instanceof client.Response) {
            if (content.dataType === 'json') {
                content = content.output;
            } else {
                content = content.html;
            }
        }

        if(content) {
            this.$.empty().append(content);
            this.applyAdditions();
            this.$.find('select:visible, input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first().focus();
            this.checkAriaLabel();
            this.updateDialogOptions();
            this.$.scrollTop(0);
        } else {
            this.close(true);
        }

        return this;
    };

    Modal.prototype.focus = function () {
        var that = this;
        setTimeout(function() {
            var $input = that.$.find('select:visible, input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first();

            if($input.data('select2')) {
                $input.select2('focus');
            } else {
                $input.focus();
            }
        }, 100);
    };

    Modal.prototype.updateDialogOptions = function() {
        this.set({
            backdrop : this.getDialog().data('backdrop'),
            keyboard : this.getDialog().data('keyboard')
        });
    };

    /**
     * Retrieves the modal-body element
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getBody = function () {
        return this.$.find('.modal-body');
    };

    var ConfirmModal = function (node, options) {
        Modal.call(this, node, options);
    };

    object.inherits(ConfirmModal, Modal);

    ConfirmModal.prototype.open = function (cfg) {
        var that = this;
        return new Promise(function (resolve, reject) {
            cfg = cfg || {};

            cfg.handler = resolve;
            cfg.reject = reject;

            that.clear();
            cfg['header'] = cfg['header'] || module.config.defaultConfirmHeader;
            cfg['body'] = cfg['body'] || module.config.defaultConfirmBody;
            cfg['confirmText'] = cfg['confirmText'] || module.config.defaultConfirmText;
            cfg['cancelText'] = cfg['cancelText'] || module.config.defaultCancelText;
            that.setHeader(cfg['header']);
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
        $cancelButton.text(cfg['cancelText']);

        var $confirmButton = this.$.find('[data-modal-confirm]');
        $confirmButton.text(cfg['confirmText']);

        //Init handler
        var that = this;
        if (cfg['handler']) {
            $confirmButton.one('click', function (evt) {
                that.clear();
                cfg['handler'](true);
            });
        }

        if (cfg['handler']) {
            $cancelButton.one('click', function (evt) {
                that.clear();
                cfg['handler'](false);
            });
        }
    };

    var init = function () {
        module.global = Modal.instance('#globalModal');
        module.global.$.on('hidden.bs.modal', function (e) {
            module.global.reset();
        });

        module.globalConfirm = ConfirmModal.instance('#globalModalConfirm');

        _setModalEnforceFocus();
        _setGlobalModalTargetHandler();

        $(document).on('show.bs.modal', '.modal', function (event) {
            $(this).appendTo($('body'));
            $(this).attr('aria-hidden', 'false');
        });

        $(document).on('shown.bs.modal', '.modal.in', function (event) {
            _setModalsAndBackdropsOrder();
        });

        $(document).on('hidden.bs.modal', '.modal', function (event) {
            _setModalsAndBackdropsOrder();
            $(this).attr('aria-hidden', 'true');
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
    };

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

                if($target.closest('.ProseMirror-prompt').length) {
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
                module.global.setDialog(response);
                if (!module.global.$.is(':visible')) {
                    module.global.show();
                }
            }).catch(function (error) {
                module.log.error(error, true);
                module.global.close();
            });
        });
    };

    var submit = function (evt, options) {
        evt.$form = evt.$form || evt.$trigger.closest('form');

        if (!evt.$form.length) {
            evt.$form = evt.$target;
        }

        var id = evt.$trigger.data('modal-id');
        if (!id) {
            // try to autodetect modal id if we're currently in a modal
            var $parent = evt.$trigger.closest('.modal');
            if ($parent.length) {
                id = $parent.attr('id');
            }
        }

        var modal = (id) ? module.get(id) : module.global;
        return client.submit(evt, setDefaultRequestData(options)).then(function (response) {
            if(response.success) {
                modal.close();
            } else {
                modal.setDialog(response);
                if (!modal.$.is(':visible')) {
                    modal.show();
                }
            }

            modal.$.trigger('submitted', response);
            return response;
        }).catch(function (error) {
            module.log.error(error, true);
            modal.close();
        });
    };

    var load = function (evt, options) {
        var id = evt.$trigger.data('modal-id');
        if (!id) {
            // try to autodetect modal id if we're currently in a modal
            var $parent = evt.$trigger.closest('.modal');
            if ($parent.length) {
                id = $parent.attr('id');
            }
        }

        var modal = (id) ? module.get(id) : module.global;
        return modal.load(evt, setDefaultRequestData(options))
            .catch(function (err) {
            module.log.error(err, true);
            modal.close();
        });
    };

    var unload = function() {
        $('.modal').each(function () {
            var modal = Modal.instance(this);
            if (modal && typeof modal.close === 'function') {
                modal.close();
            }
        });
    }

    var post = function (evt, options) {
        var id = evt.$trigger.data('modal-id');
        if (!id) {
            // try to autodetect modal id if we're currently in a modal
            var $parent = evt.$trigger.closest('.modal');
            if ($parent.length) {
                id = $parent.attr('id');
            }
        }

        var modal = (id) ? module.get(id) : module.global;
        return modal.post(evt, setDefaultRequestData(options)).catch(function (err) {
            module.log.error(err, true);
            modal.close();
        });
    };

    var show = function (evt) {
        var modal = get(evt.$target);
        if(modal) {
            modal.show();
        }
    };

    var get = function (id, options) {
        var modal = !(modals[id]) ? new Modal(id) : modals[id];
        if (options) {
            modal.set(options);
        }
        return modal;
    };

    var confirm = function (evt) {
        if (!(evt instanceof $.Event || evt instanceof $)) { // Simple config given
            return module.globalConfirm.open(evt);
        }

        var confirmOptions = (evt instanceof $.Event) ? _getConfirmOptionsByTrigger(evt.$trigger) : _getConfirmOptionsByTrigger(evt);

        return module.confirm(confirmOptions);
    };

    var _getConfirmOptionsByTrigger = function ($trigger) {
        return {
            'body': $trigger.data('action-confirm'),
            'header': $trigger.data('action-confirm-header') ||  $trigger.data('action-confirm-title'),
            'confirmText': $trigger.data('action-confirm-text'),
            'cancelText': $trigger.data('action-cancel-text')
        };
    };

    module.export({
        init: init,
        sortOrder: 100,
        confirm: confirm,
        Modal: Modal,
        ConfirmModal: ConfirmModal,
        get: get,
        post: post,
        load: load,
        unload: unload,
        show: show,
        submit: submit
    });
});
