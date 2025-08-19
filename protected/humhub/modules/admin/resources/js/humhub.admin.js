humhub.module('admin', function (module, require, $) {
    var client = require('client');
    var modal = require('ui.modal');
    var additions = require('ui.additions');
    var status = require('ui.status');

    /**
     * Action will delete the current page logo.
     * This will trigger a confirm modal.
     *
     * @param {type} evt
     * @returns {undefined}
     */
    var deletePageLogo = function (evt) {
        evt.finish();

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
            $('#logo-image').attr('src', '').hide();
            additions.switchButtons($('#img-logo'), $('#text-logo'));
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
                $('#logo-image').attr('src', e.target.result).show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };


    /**
     * Action will delete the current page icon.
     * This will trigger a confirm modal.
     *
     * @param {type} evt
     * @returns {undefined}
     */
    var deletePageIcon = function (evt) {
        evt.finish();

        var options = {
            'header': module.text('confirm.deleteIcon.header'),
            'body': module.text('confirm.deleteIcon.body'),
            'confirmText': module.text('confirm.deleteIcon.confirm')
        };

        modal.confirm(options).then(function ($confirmed) {
            if ($confirmed) {
                _confirmDeletePageIcon(evt);
            }
        });
    };

    var _confirmDeletePageIcon = function (evt) {
        client.post(evt).then(function () {
            $('#deleteIconImage').fadeOut();
            $('#icon-image').attr('src', '').hide();
            additions.switchButtons($('#img-icon'), ('#text-icon'));
        });
    };

    /**
     * Action for changing the form image.
     *
     * @param {type} evt
     * @returns {undefined}
     */
    var changeIcon = function (evt) {
        var input = evt.$trigger[0];
        if (input.files && input.files.length) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#icon-image').attr('src', e.target.result).show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var deleteLoginBg = function (evt) {
        evt.finish();

        var options = {
            'header': module.text('confirm.deleteLoginBg.header'),
            'body': module.text('confirm.deleteLoginBg.body'),
            'confirmText': module.text('confirm.deleteLoginBg.confirm')
        };

        modal.confirm(options).then(function ($confirmed) {
            if ($confirmed) {
                _confirmDeleteLoginBg(evt);
            }
        });
    };

    var _confirmDeleteLoginBg = function (evt) {
        client.post(evt).then(function () {
            $('#deleteLoginBg').fadeOut();
            $('#loginBg-image').attr('src', '').hide();
            additions.switchButtons($('#img-loginBg'), ('#text-loginBg'));
        });
    };

    var changeLoginBg = function (evt) {
        var input = evt.$trigger[0];
        if (input.files && input.files.length) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#loginBg-image').attr('src', e.target.result).show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };
    var init = function () {
        if ($('#admin-logo-file-upload').length) {
            // Forward file to chooser.
            $('#admin-logo-upload-button').on('click', function (evt) {
                evt.preventDefault();
                $('#admin-logo-file-upload').trigger('click');
            });
        }

        if ($('#admin-icon-file-upload').length) {
            // Forward file to chooser.
            $('#admin-icon-upload-button').on('click', function (evt) {
                evt.preventDefault();
                $('#admin-icon-file-upload').trigger('click');
            });
        }

        if ($('#admin-loginBg-file-upload').length) {
            // Forward file to chooser.
            $('#admin-loginBg-upload-button').on('click', function (evt) {
                evt.preventDefault();
                $('#admin-loginBg-file-upload').trigger('click');
            });
        }
    };

    var changeIndividualProfilePermissions = function (evt) {
        evt.finish();
        evt.$trigger.prop('checked', !evt.$trigger.prop('checked'));
        evt.$trigger.data('action-confirm', module.text('enableProfilePermissions.question.' + (evt.$trigger.prop('checked') ? 'disable' : 'enable')));
        evt.$trigger.data('action-confirm-text', module.text('enableProfilePermissions.button.' + (evt.$trigger.prop('checked') ? 'disable' : 'enable')));
        $.ajax({
            url: evt.$trigger.data('action-url'),
            type: "POST",
            data: {isEnabled: evt.$trigger.prop('checked')},
        }).done(function (data) {
            module.log.success('success.saved');
        });
    };

    var moduleSetAsDefault = function (event) {
        modal.footerLoader(event);
        client.submit(event).then(function (response) {
            modal.global.setDialog(response.data);
            status.success(module.require('log').config.text['success.saved']);
        });
    };

    module.export({
        init: init,
        initOnPjaxLoad: true,
        deletePageLogo: deletePageLogo,
        changeLogo: changeLogo,
        deletePageIcon: deletePageIcon,
        changeIcon: changeIcon,
        deleteLoginBg: deleteLoginBg,
        changeLoginBg: changeLoginBg,
        changeIndividualProfilePermissions: changeIndividualProfilePermissions,
        moduleSetAsDefault: moduleSetAsDefault,
    });
});
