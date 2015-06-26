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

    var activityLastLoadedEntryId = "";

    // save if the last entries are already loaded
    var activityLastEntryReached = false;

    // listen for scrolling event yes or no
    var scrolling = true;

    // hide loader
    $("#activityLoader").hide();

    $('#activityContents').scroll(function () {

        // save height of the overflow container
        var _containerHeight = $("#activityContents").height();

        // save scroll height
        var _scrollHeight = $("#activityContents").prop("scrollHeight");

        // save current scrollbar position
        var _currentScrollPosition = $('#activityContents').scrollTop();

        // load more activites if current scroll position is near scroll height
        if (_currentScrollPosition >= (_scrollHeight - _containerHeight - 30)) {

            // checking if ajax loading is necessary or the last entries are already loaded
            if (activityLastEntryReached == false) {

                if (scrolling == true) {

                    // stop listening for scrolling event to load the new activity range only one time
                    scrolling = false;

                    // load more activities
                    loadMoreActivities();
                }
            }

        }

    });

    /**
     * load new activities
     */
    function loadMoreActivities() {



        // save url for activity reloads
        var _url = activityStreamUrl.replace('-from-', activityLastLoadedEntryId);

        // show loader
        $("#activityLoader").show();

        // load json
        jQuery.getJSON(_url, function (json) {

            if (activityLastLoadedEntryId == "" && json.counter == 0) {

                // show placeholder if no results exists
                $("#activityEmpty").show();

                // hide loader
                $("#activityLoader").hide();

            } else {

                // add new activities
                $("#activityLoader").before(json.output);

                // save the last activity id for the next reload
                activityLastLoadedEntryId = json.lastEntryId;

                if (json.counter < 10) {
                    // prevent the next ajax calls, if there are no more entries
                    activityLastEntryReached = true;

                    // hide loader
                    $("#activityLoader").hide();
                }

            }

            // start listening for the scrolling event
            scrolling = true;

        });
    }

    // load the first activities
    loadMoreActivities();

});

