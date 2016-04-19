$(document).on('click', '.unfollowSpaceButton', function (event) {
    event.preventDefault();
    var spaceid = $(this).data("spaceid");
    $.ajax({
        url: $(this).attr("href"),
        type: "POST",
        success: function () {
            $(".unfollowSpaceButton[data-spaceid='" + spaceid + "']").hide();
            $(".followSpaceButton[data-spaceid='" + spaceid + "']").show();
        }
    });
    
});

$(document).on('click', '.followSpaceButton', function (event) {
    event.preventDefault();
    var spaceid = $(this).data("spaceid");
    $.ajax({
        url: $(this).attr("href"),
        type: "POST",
        success: function () {
            $(".unfollowSpaceButton[data-spaceid='" + spaceid + "']").show();
            $(".followSpaceButton[data-spaceid='" + spaceid + "']").hide();
        }
    });
    
});