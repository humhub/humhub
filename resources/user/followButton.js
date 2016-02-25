$(document).on('click', '.unfollowButton', function (event) {
    var userId = $(this).data("userid");
    $.ajax({
        url: $(this).attr("href"),
        type: "POST",
        success: function () {
            $(".unfollowButton[data-userid='" + userId + "']").hide();
            $(".followButton[data-userid='" + userId + "']").show();
        }
    });
    event.preventDefault();
});

$(document).on('click', '.followButton', function (event) {
    var userId = $(this).data("userid");
    $.ajax({
        url: $(this).attr("href"),
        type: "POST",
        success: function () {
            $(".unfollowButton[data-userid='" + userId + "']").show();
            $(".followButton[data-userid='" + userId + "']").hide();
        }
    });
    event.preventDefault();
});