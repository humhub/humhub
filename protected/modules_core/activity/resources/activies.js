/**
 * Click on an Activity wall Entry
 */
function activityShowItem(wallEntryId) {

    if (mainStream) {
        mainStream.showItem(wallEntryId);
    } else {
        // Redirect to Permalink
        window.location.replace(activityPermaLinkUrl + "?id=" + wallEntryId);
    }
}


$(document).ready(function () {


    // set the ID for the last loaded activity entry to 1
    var activityLastLoadedEntryId = 1;

    // save if the last entries are already loaded
    var activityLastEntryReached = false;

    $('#activityContents').scroll(function () {

        // save height of the overflow container
        var _containerHeight = $("#activityContents").height();

        // save scroll height
        var _scrollHeight = $("#activityContents").prop("scrollHeight");

        // save current scrollbar position
        var _currentScrollPosition = $('#activityContents').scrollTop();

        // load more activites if current scroll position is near scroll height
        if (_currentScrollPosition >= (_scrollHeight - _containerHeight - 1)) {

            // checking if ajax loading is necessary or the last entries are already loaded
            if (activityLastEntryReached == false) {

                // load more activities
                loadMore();
            }

        }

    });

    /**
     * load new activities
     */
    function loadMore() {

        // save url for activity reloads
        var _url = activityReloadUrl.replace('lastEntryId', activityLastLoadedEntryId);

        if (activityLastLoadedEntryId == 1) {
            // save url for the first loading
            _url = activityStartUrl;
        }

        // show loader
        $("#activityLoader").show();

        // load json
        jQuery.getJSON(_url, function (json) {

            // hide loader
            $("#activityLoader").hide();

            if (activityLastLoadedEntryId == 1 && json.counter == 0) {

                // show placeholder if no results exists
                $("#activityEmpty").show();

            } else {

                // add new activities
                $('#activityContents').prepend(json.output);

                // save the last activity id for the next reload
                activityLastLoadedEntryId = json.lastEntryId;

                if (json.counter < 10) {
                    // prevent the next ajax calls, if there are no more entries
                    activityLastEntryReached = true;
                }

            }

        });
    }

    // load the first activities
    loadMore();

});

