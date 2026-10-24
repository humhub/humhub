humhub.module('user.login', function (module, require, $) {
    const additions = require('ui.additions');

    // Email/username fields of the auth forms (Sign In, Sign Up, Password recovery).
    // The value typed in one of them is pre-filled in the next one the user opens.
    const AUTH_EMAIL_FIELDS = '#login_username, #register-email, #email_txt';
    const STORAGE_EMAIL_KEY = 'humhub.user.login.email';

    var init = function () {
        $('body').on('click', '.authChoice .auth-link', function (e) {
            var checked = $('#login-rememberme').is(':checked');
            var $this = $(this);
            var original = $this.data('originalUrl');

            if (!original) {
                original = $this.attr('href');
                $this.data('originalUrl', original);
            }

            var url = new URL(original, window.location.origin);
            if (checked) {
                url.searchParams.set('rememberMe', 1);
            }

            $this.attr('href', url.toString());
        });

        initEmailPrefill();
    };

    const initEmailPrefill = function () {
        let storage;
        try {
            storage = window.sessionStorage;
        } catch (e) {
            // Site data is blocked by the browser
            return;
        }
        if (!storage) {
            // WebView without DOM storage
            return;
        }

        if (!require('user').isGuest()) {
            // Logged in: forget the email, so it isn't pre-filled after logout
            storage.removeItem(STORAGE_EMAIL_KEY);
            return;
        }

        $('body').on('input change', AUTH_EMAIL_FIELDS, function () {
            // Remember the email in the storage
            storage.setItem(STORAGE_EMAIL_KEY, String($(this).val() || '').trim());
        });

        // Applied on page load and on every modal content swap
        additions.register('user.login.email', AUTH_EMAIL_FIELDS, function ($match) {
            // Get the email from the storage
            const email = storage.getItem(STORAGE_EMAIL_KEY);
            if (!email) {
                return;
            }

            $match.each(function () {
                const $field = $(this);
                // Only the Sign In field accepts a plain username
                if (!$field.val() && ($field.is('#login_username') || email.indexOf('@') > 0)) {
                    $field.val(email);
                }
            });
        });
    };

    var delayLoginAction = function (delaySeconds, message, buttonSelector) {
        var originalLoginButtonText = $(buttonSelector).html();
        $(buttonSelector).html(message + " (" + delaySeconds + ")").prop('disabled', true);

        var delayTimer = setInterval(function () {
            $(buttonSelector).html(message + " (" + --delaySeconds + ")");
            if (delaySeconds <= 0) {
                clearInterval(delayTimer);
                $(buttonSelector).html(originalLoginButtonText).prop('disabled', false);
            }
        }, 1000);
    }

    module.export({
        init: init,
        delayLoginAction: delayLoginAction,
    });
});
