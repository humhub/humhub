/**
 * Warns before the session ends for lack of activity, so it can be extended with one button (WCAG 2.2.1).
 *
 * The server ends a session `timeout` seconds after its last request (`yii\web\User::$authTimeout`); every request
 * renews it. So the time runs from the last successful request of any tab (shared through `localStorage`). Two
 * minutes before the end:
 *
 * - if the user typed, clicked or scrolled since that request (a long post being written), the session is renewed
 *   silently
 * - else a dialog asks "Are you still there?": its button, Escape or closing it renew the session
 *
 * Once the time is up, the dialog says the user was signed out and offers to sign in again. Without a time limit
 * (a remembered login, `timeout` 0) the module does nothing.
 *
 * @since 1.20
 */
humhub.module('user.session', function (module, require, $) {
    var client = require('client');

    var STORAGE_KEY = 'humhub.user.session.contact';
    var WARN_BEFORE = 120 * 1000;
    var DIALOG_ID = 'user-session-dialog';

    var lastContact = 0;
    var lastActivity = 0;
    var timer = null;
    var countdown = null;
    // null, 'warning' or 'ended'
    var state = null;
    var renewing = false;
    var started = false;

    var getTimeout = function () {
        return (module.config.timeout || 0) * 1000;
    };

    // At least 20 s to answer, also with a short timeout
    var getWarnBefore = function () {
        return Math.min(WARN_BEFORE, getTimeout() / 2);
    };

    var getRemaining = function () {
        return lastContact + getTimeout() - Date.now();
    };

    var init = function () {
        if (!getTimeout() || started) {
            return;
        }
        started = true;

        contact(Date.now());

        $(document).on('ajaxSuccess.humhubUserSession', function () {
            contact(Date.now());
        });
        ['keydown', 'pointerdown', 'wheel', 'touchstart'].forEach(function (type) {
            document.addEventListener(type, onActivity, {capture: true, passive: true});
        });
        window.addEventListener('storage', onStorage);
        // A timer of a hidden tab runs late, a sleeping device's not at all
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                check();
            }
        });
    };

    var onActivity = function () {
        lastActivity = Date.now();
    };

    var onStorage = function (evt) {
        if (evt.key === STORAGE_KEY && evt.newValue) {
            contact(parseInt(evt.newValue, 10));
        }
    };

    /**
     * A request reached the server, which renewed the session
     *
     * @param {number} time
     */
    var contact = function (time) {
        if (state === 'ended' || !(time > lastContact)) {
            return;
        }

        lastContact = time;
        try {
            if (parseInt(window.localStorage.getItem(STORAGE_KEY), 10) !== time) {
                window.localStorage.setItem(STORAGE_KEY, String(time));
            }
        } catch (e) {
            // No storage: each tab counts on its own
        }

        // Renewed elsewhere (another tab, a request of the page): the question is answered
        if (state === 'warning') {
            state = null;
            closeDialog();
        }
        schedule();
    };

    var schedule = function () {
        clearTimeout(timer);
        timer = setTimeout(check, Math.max(0, getRemaining() - getWarnBefore()));
    };

    var check = function () {
        if (state === 'ended' || !lastContact) {
            return;
        }

        if (getRemaining() <= 0) {
            showEnded();
        } else if (getRemaining() > getWarnBefore()) {
            schedule();
        } else if (lastActivity > lastContact) {
            renew();
        } else if (state !== 'warning') {
            showWarning();
        }
    };

    /**
     * Renews the session with a request of its own
     *
     * @returns {Promise}
     */
    var renew = function () {
        if (renewing) {
            return renewing;
        }

        // JSON, so a session that ended answers 403 instead of sending the page to the sign in
        renewing = client.post(module.config.keepAliveUrl, {dataType: 'json'}).then(function () {
            contact(Date.now());
        }).catch(function (response) {
            if (response && (response.status === 401 || response.status === 403)) {
                showEnded();
            } else {
                module.log.warn('Could not extend the session', response);
                schedule();
            }
        }).finally(function () {
            renewing = false;
        });

        return renewing;
    };

    var getDialog = function () {
        var $dialog = $('#' + DIALOG_ID);
        if ($dialog.length) {
            return $dialog;
        }

        // Bootstrap's own focus trap stays off like in every dialog: `ui.modal` keeps the focus inside
        $dialog = $('<div class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-focus="false">'
            + '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">'
            + '<div class="modal-header"><h2 class="modal-title h5"></h2></div>'
            + '<div class="modal-body"><p class="mb-0"></p><div class="visually-hidden" role="alert"></div></div>'
            + '<div class="modal-footer"><button type="button" class="btn btn-primary"></button></div>'
            + '</div></div></div>')
            .attr({id: DIALOG_ID, 'aria-labelledby': DIALOG_ID + '-title', 'aria-describedby': DIALOG_ID + '-text'})
            .appendTo('body');
        $dialog.find('.modal-title').attr('id', DIALOG_ID + '-title');
        $dialog.find('.modal-body p').attr('id', DIALOG_ID + '-text');

        $dialog.on('shown.bs.modal', function () {
            $dialog.find('.modal-footer .btn').trigger('focus');
        }).on('hidden.bs.modal', function () {
            clearInterval(countdown);
            // Answered: the user is there
            if (state === 'warning') {
                state = null;
                renew();
            }
        }).on('click', '.modal-footer .btn', function () {
            if (state === 'ended') {
                // The server sends the sign in and returns to this page
                window.location.reload();
            } else {
                closeDialog();
            }
        });

        return $dialog;
    };

    var formatRemaining = function () {
        var seconds = Math.max(0, Math.ceil(getRemaining() / 1000));
        return Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0');
    };

    var showWarning = function () {
        state = 'warning';
        var $dialog = getDialog();
        $dialog.find('[role="alert"]').empty();
        var parts = module.text('warning').split('{time}');
        var $time = $('<span>').text(formatRemaining());

        $dialog.find('.modal-title').text(module.text('warningTitle'));
        $dialog.find('.modal-body p').empty().append(document.createTextNode(parts[0]), $time,
            document.createTextNode(parts.slice(1).join('{time}')));
        $dialog.find('.modal-footer .btn').text(module.text('stay'));

        clearInterval(countdown);
        countdown = setInterval(function () {
            if (getRemaining() <= 0) {
                showEnded();
            } else {
                $time.text(formatRemaining());
            }
        }, 1000);

        bootstrap.Modal.getOrCreateInstance($dialog[0]).show();
    };

    var showEnded = function () {
        state = 'ended';
        clearTimeout(timer);
        clearInterval(countdown);

        var $dialog = getDialog();
        $dialog.find('.modal-title').text(module.text('endedTitle'));
        $dialog.find('.modal-body p').text(module.text('ended'));
        $dialog.find('.modal-footer .btn').text(module.text('signIn'));

        if ($dialog.hasClass('show')) {
            // The open warning turns into this: said once, the focus stays on the button
            $dialog.find('[role="alert"]').text(module.text('endedTitle') + '. ' + module.text('ended'));
        } else {
            bootstrap.Modal.getOrCreateInstance($dialog[0]).show();
        }
    };

    var closeDialog = function () {
        var $dialog = $('#' + DIALOG_ID);
        if ($dialog.length) {
            bootstrap.Modal.getOrCreateInstance($dialog[0]).hide();
        }
    };

    module.export({
        init: init,
        renew: renew,
    });
});
