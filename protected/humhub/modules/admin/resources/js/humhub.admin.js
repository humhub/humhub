humhub.module('admin', function (module, require, $) {
    var client = require('client');
    var modal = require('ui.modal');
    var additions = require('ui.additions');

    /**
     * Action will delete the current page logo.
     * This will trigger a confirm modal.
     * 
     * @param {type} evt
     * @returns {undefined}
     */
    var deletePageLogo = function (evt) {
        var options = {
            'header': module.text('confirm.deleteLogo.header'),
            'body': module.text('confirm.deleteLogo.body'),
            'confirmText': module.text('confirm.deleteLogo.confirm')
        };

        modal.confirm(options).then(function ($confirmed) {
            if ($confirmed) {
                _confirmDeletePageLogo(evt);
            }
        });
    };

    var _confirmDeletePageLogo = function (evt) {
        client.post(evt).then(function () {
            $('#deleteLogoImage').fadeOut();
            $('#logo-image').attr('src', '');
            additions.switchButtons($('#img-logo'), ('#text-logo'));
        });
    };

    /**
     * Action for changing the form image.
     * 
     * @param {type} evt
     * @returns {undefined}
     */
    var changeLogo = function (evt) {
        var input = evt.$trigger[0];
        if (input.files && input.files.length) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#logo-image').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var init = function () {
        if (!$('#admin-logo-file-upload').length) {
            return;
        }

        // Forward file to chooser.
        $('#admin-logo-upload-button').on('click', function (evt) {
            evt.preventDefault();
            $('#admin-logo-file-upload').trigger('click');
        });
    };

    module.export({
        init: init,
        initOnPjaxLoad: true,
        deletePageLogo: deletePageLogo,
        changeLogo: changeLogo
    });
});