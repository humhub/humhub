humhub.module('user.login', function(module, require, $) {

    var init = function() {
        $('body').on('click', '.authChoice .auth-link', function(e) {
            var checked = $('#login-rememberme').is(':checked');
            var $this = $(this);
            var original = $this.data('originalUrl');

            if(!original) {
                original = $this.attr('href');
                $this.data('originalUrl', original);
            }

            $this.attr('href', checked ?  original + '&rememberMe=1' : original);
        });

    };
    
    module.export({
        init: init
    });
});
