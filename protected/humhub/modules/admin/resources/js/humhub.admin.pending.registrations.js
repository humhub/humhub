humhub.module('admin.pending.registrations', function (module, require, $) {
    module.initOnPjaxLoad = true;

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

    var init = function (isPjax) {

        $("input").change(function () {
            var $checkBoxes = $('.regular-checkbox');

            if (hasChecked($checkBoxes)) {
                $('.btn-delete').html('Delete selected rows')
            } else {
                $('.btn-delete').html('Delete all')
            }
        });

        $(".btn-delete").on("click", function (e) {
            var keys = $("#grid").yiiGridView("getSelectedRows");
            if (keys.length > 0) {
                e.preventDefault();
                if(confirm("Delete selected invitations?")){
                    $.ajax({
                        url: "/index.php?r=admin%2Fpending-registrations%2Fdelete-all",
                        type: "POST",
                        data: {id: keys},
                        success: function () {
                            alert("yes")
                        }
                    })
                } else {
                    window.location = "index.php?r=admin%2Fpending-registrations";
                }
            }
        });

    };

    module.export({
        init: init,
    });
});
