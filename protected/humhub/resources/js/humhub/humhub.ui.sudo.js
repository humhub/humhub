/**
 * The sudo mode on the client (docs/develop/concept-sudo-mode.md): the confirmation dialog for requests that ask for
 * it, and the original request again afterwards.
 *
 * - `humhub.client` hands every `401` with the `X-Sudo-Required` header to `confirm()` and repeats the request
 * - a form marked `data-sudo-required` (`ActiveForm::$sudo`) asks before it is sent
 * - a pjax navigation into a protected page asks, then navigates again
 *
 * @since 1.20
 */
humhub.module('ui.sudo', function (module, require, $) {
    var client = require('client');
    var modal = require('ui.modal');
    var loader = require('ui.loader');

    var pending = null;

    /**
     * Opens the confirmation dialog.
     *
     * @param {string} url the confirmation URL (with its return URL)
     * @returns {Promise} resolved once the user confirmed, rejected when the dialog is closed without
     */
    var confirm = function (url) {
        if (pending) {
            return pending;
        }

        pending = new Promise(function (resolve, reject) {
            var dialog = modal.global;
            var confirmed = false;

            var onSubmitted = function (evt, response) {
                if (response && response.success) {
                    confirmed = true;
                }
            };

            // Bootstrap may report `hidden` before the modal triggers `submitted` (no transition): decide afterwards
            dialog.$.one('hidden.bs.modal', function () {
                setTimeout(function () {
                    dialog.$.off('submitted.humhub-sudo', onSubmitted);
                    pending = null;
                    confirmed ? resolve() : reject();
                }, 0);
            });

            // Loading resets the modal's `submitted` listeners, so this one follows the load
            dialog.load(url).then(function () {
                dialog.$.on('submitted.humhub-sudo', onSubmitted);
            }).catch(function (error) {
                module.log.error(error, true);
                dialog.close();
            });
        });

        return pending;
    };

    var onFormSubmit = function (evt) {
        var form = evt.currentTarget;
        var url = form.getAttribute('data-sudo-required');
        if (!url) {
            return;
        }

        evt.preventDefault();
        evt.stopImmediatePropagation();

        confirm(url).then(function () {
            form.removeAttribute('data-sudo-required');
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }, function () {
            // Cancelled: the form stays as it is, without the loader its button started on the click
            $(form).find('[data-ui-loader]').each(function () {
                loader.reset(this);
            });
        });
    };

    /**
     * Enter in the password field sends the dialog like its Confirm button does, not as a page request
     */
    var onDialogFormSubmit = function (evt) {
        evt.preventDefault();
        var button = evt.currentTarget.querySelector('[data-action-click="ui.modal.submit"]');
        if (button) {
            button.click();
        }
    };

    // The page a pjax navigation starts from: pjax shows the requested URL as soon as it sends the request
    var pjaxOrigin = null;

    var onPjaxBeforeSend = function () {
        pjaxOrigin = {url: window.location.href, state: window.history.state};
    };

    var onPjaxError = function (evt, xhr, textStatus, errorThrown, options) {
        var url = xhr && xhr.status === 401 && xhr.getResponseHeader ? xhr.getResponseHeader('X-Sudo-Required') : null;
        if (!url) {
            return;
        }

        evt.preventDefault();
        var origin = pjaxOrigin;
        confirm(url).then(function () {
            client.redirect(options.url);
        }, function () {
            // Cancelled: the user stays on the current page, with its address
            if (origin && window.location.href !== origin.url) {
                window.history.replaceState(origin.state, '', origin.url);
            }
        });
    };

    var init = function (isPjax) {
        if (isPjax) {
            return;
        }

        client.setSudoHandler(confirm);
        $(document).on('submit.humhub-sudo', 'form[data-sudo-required]', onFormSubmit);
        $(document).on('submit.humhub-sudo', '#sudo-confirm-modal form', onDialogFormSubmit);
        $(document).on('pjax:beforeSend.humhub-sudo', onPjaxBeforeSend);
        $(document).on('pjax:error.humhub-sudo', onPjaxError);
    };

    module.export({
        init: init,
        confirm: confirm
    });
});
