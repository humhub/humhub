/**
 * Inits the Like Module
 * 
 * This function should executed after a new like link appears
 */
function initLikeModule() {

    // Remove Existing Handlers
    $('.likeAnchor').off("click");

    // Handle Click on a Like Button
    $('.likeAnchor').on("click", function (event) {
        event.preventDefault();
        var likeContainerDiv = $(this).closest(".likeLinkContainer");

        $.ajax({
            dataType: "json",
            type: 'POST',
            url: $(this).attr("href")
        }).done(function (data) {
            likeContainerDiv.find('.likeAnchor').hide();

            if (data.currentUserLiked) {
                likeContainerDiv.find('.unlike').show();
            } else {
                likeContainerDiv.find('.like').show();
            }

            updateLikeCounters(likeContainerDiv, data.likeCounter);
        });

    });

}

/**
 * Updates the Like Counters
 * 
 * This function will be called by ShowLikesWidget.
 * 
 * @param {type} className
 * @param {type} id
 * @param {type} count
 * @returns {undefined} 
 */
function updateLikeCounters(element, count) {

    if (count != 0) {
        element.find(".likeCount").show();
        element.find(".likeCount").html('(' + count + ')');
    } else {
        element.find(".likeCount").hide();
    }

}
