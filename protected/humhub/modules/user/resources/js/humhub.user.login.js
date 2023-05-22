humhub.module('user.login', function (module, require, $) {

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
            if(checked) {
                url.searchParams.set('rememberMe', 1);
            }

            $this.attr('href', url.toString());
        });

    };

    var delayLoginAction = function (delaySeconds, message, buttonSelector) {
        var originalLoginButtonText = $(buttonSelector).html();
        $(buttonSelector).html(message + " (" + delaySeconds + ")").prop('disabled', true);

        var delayTimer = setInterval(function() {
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
