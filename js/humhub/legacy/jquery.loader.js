/*
 * Loader v0.1 by @andystrobel
 * Copyright 2015 HumHub
 *
 * Show loader
 * @deprecated since v1.2
 */


//
// create closure
//
(function ($) {
    //
    // plugin definition
    //
    $.fn.loader = function (options) {

        // build main options before element iteration
        var opts = $.extend({}, $.fn.loader.defaults, options);

        function buildModal(message) {

            var _modal = '<div class="modal" id="loaderModal">' +
                '<div class="modal-dialog modal-dialog-extra-small" style="">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h4 class="modal-title" id="myModalLabel">' + message + '</h4>' +
                '</div>' +
                '<div class="modal-body text-center">' +
                '<div class="loader" style="padding: 0 0 20px 0"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'

            $('body').append(_modal);
            $('#loaderModal').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });


        }

        // iterate and reformat each matched element
        return this.each(function () {

            // save object in a variable
            $this = $(this);

            // unbind all previous event handler
            $this.unbind();

            $this.click(function () {
                // build modal and add it to DOM
                buildModal($this.data("message"));
            })

        });

    };

    //
    // plugin defaults
    //
    $.fn.loader.defaults = {};
//
// end of closure
//
})(jQuery);