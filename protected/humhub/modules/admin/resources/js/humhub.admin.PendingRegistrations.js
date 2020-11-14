humhub.module('admin.PendingRegistrations', function (module, require, $) {
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;
    var client = require('client');
    var urlDeleteSelected;
    var urlDeleteAll;
    var noteDeleteSelected;
    var noteDeleteAll;


    var PendingRegistrations = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(PendingRegistrations, Widget);

    PendingRegistrations.deleteAllSelected = function (evt) {
        client.post(evt).then(function () {
            var keys = $("#grid").yiiGridView("getSelectedRows");
            $.ajax({
                url: urlDeleteSelected,
                type: "POST",
                data: {id: keys},
            })
        }).catch(function (e) {
            module.log.error(e, true);
        })
    };

    PendingRegistrations.deleteAll = function (evt) {
        client.post(evt).then(function () {
            that.reload();
        }).catch(function (e) {
            module.log.error(e, true);
        })
    };

    function hasChecked($checkBoxes) {
        var result = false;
        $checkBoxes.each(function () {
            if ($(this).prop("checked")) {
                result = true;
                return false;
            }
        });
        return result;
    }

    PendingRegistrations.prototype.init = function () {
        urlDeleteSelected = this.options.urlDeleteSelected;
        urlDeleteAll = this.options.urlDeleteAll;
        noteDeleteSelected = this.options.noteDeleteSelected;
        noteDeleteAll = this.options.noteDeleteAll;

        this.$.find("input").change(function () {

            var $checkBoxes = $('.regular-checkbox');

            if (hasChecked($checkBoxes)) {
                $('.delete-all').html('Delete selected rows')
                $('.delete-all').attr('data-action-click', 'admin.PendingRegistrations.deleteAllSelected');
                $('.delete-all').attr('data-action-click-url', urlDeleteSelected);
            } else {
                $('.delete-all').html('Delete all')
                $('.delete-all').attr('data-action-click', 'admin.PendingRegistrations.deleteAll');
                $('.delete-all').attr('data-action-click-url', urlDeleteAll);
            }
        });
    };

    // Export a single class
    module.export = PendingRegistrations;
});
