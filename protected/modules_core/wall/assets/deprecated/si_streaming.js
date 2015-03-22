// Defines current active stream object
var currentStream;

// Defines the main stream
var mainStream;


/**
 * Stream Class
 *
 */
function Stream(baseElement, urlStart, urlReload, urlSingle) {


    // Main Elemente
    this.baseDiv = baseElement;

    this.urlStart = urlStart;
    this.urlReload = urlReload;
    this.urlSingle = urlSingle;

    this.filters = "";

    this.sorting = "c";

    /**
     * Return parseHtml result and delete dublicated entries from container
     * @param {object} json JSON object
     * @param {object} container Container with entries
     * @returns {string} HTML string
     */
    function parseEntriesHtml(json, container) {
        function removeDublicates(entryIds, container) {
            for (var i = 0, count = entryIds.length; i < count; i++) {
                if ($(container).find('#wallEntry_' + entryIds[i]).length) {
                    $("#wallEntry_" + entryIds[i]).remove();
                }
            }
        }
        if (typeof container !== 'undefined') {
            removeDublicates(json.entryIds, container);
        }
        return parseHtml(json.output);
    }

    /**
     * Clear Stream
     */
    this.clear = function () {

        this.lastLoadedEntryId = 0;
        //this.firstLoadedEntryId = 0;

        this.loadedEntryCount = 0;
        this.lastEntryLoaded = false;		// Maximum wallentries reached
        this.currentMode = 'normal';		// or single


        this.readOnly = false;

        this.loadingBusyFlag = false;		// Flag active for scroller

        // Empty loaded stuff
        $(this.baseDiv).find(".s2_singleContent").empty();
        $(this.baseDiv).find(".s2_streamContent").empty();

        // Default Hides
        $(this.baseDiv).find(".s2_stream").hide();
        $(this.baseDiv).find(".s2_single").hide();
        $(this.baseDiv).find(".streamLoader").hide();
        $(this.baseDiv).find(".streamLoaderSingle").hide();
        $(this.baseDiv).find(".emptyStreamMessage").hide();
        $(this.baseDiv).find(".emptyFilterStreamMessage").hide();

        this.hideFilter();

    }


    /**
     * Mark Stream as Read Only
     */
    this.markAsReadOnly = function () {
        this.readOnly = true;

        $(this.baseDiv).find(".s2_single").show();

    }


    /**
     * Shows a single Item
     *
     * @param wallEntryId to show
     */
    this.showItem = function (wallEntryId) {
        me = this;

        if (typeof activeTab != 'undefined') {
            if (activeTab != 0) {
                // When in workspaces with multiple tabs, ensure that
                // Stream Tab is activated
                $('#tabs_panel').tabs({
                    selected: 0
                });
            }
        }

        this.clear();
        $(this.baseDiv).find(".s2_single").show();
        $(this.baseDiv).find(".streamLoaderSingle").show();

        this.currentMode = 'single';

        url = this.urlSingle;
        url = url.replace('fromEntryId', wallEntryId + 1);

        // Load Entry
        jQuery.getJSON(url, function (json) {
            streamDiv = $(me.baseDiv).find(".s2_singleContent");
            //$(json.output).appendTo(streamDiv).fadeIn('fast');
            $(streamDiv).append(parseEntriesHtml(json,streamDiv ));

            me.lastEntryLoaded = true;
            me.loadedEntryCount += json.counter;
            $(me.baseDiv).find(".streamLoaderSingle").hide();

            // Back Link
            $(me.baseDiv).find(".singleBackLink").off('click');
            $(me.baseDiv).find(".singleBackLink").click(function () {
                me.showStream();
            });


            me.onNewEntries();


        });


    }


    /**
     * Shows the Stream
     */
    this.showStream = function () {

        me = this;

        this.clear();
        $(this.baseDiv).find(".s2_stream").show();
        $(this.baseDiv).find(".streamLoader").show();

        this.currentMode = 'stream';

        url = this.urlStart;
        url = url.replace('filter_placeholder', this.filters);
        url = url.replace('sort_placeholder', this.sorting);

        jQuery.getJSON(url, function (json) {

            $(me.baseDiv).find(".streamLoader").hide();
            me.showFilter();

            if (json.counter == 0) {
                me.lastEntryLoaded = true;
                $('#btn-load-more').hide();

                if (me.filters == "") {
                    me.hideFilter();
                    $(me.baseDiv).find(".emptyStreamMessage").show();
                } else {
                    $(me.baseDiv).find(".emptyFilterStreamMessage").show();
                }

                return;
            }

            me.loadedEntryCount += json.counter;

            streamDiv = $(me.baseDiv).find(".s2_streamContent");
            //$(json.output).appendTo(streamDiv).fadeIn('fast');
            $(streamDiv).append(parseEntriesHtml(json,streamDiv ));

            me.lastLoadedEntryId = json.lastEntryId;
            me.onNewEntries();
            me.loadMore();
            installAutoLoader();

        });

    }


    /**
     * Shows the Stream
     */
    this.deleteEntry = function (wallEntryId) {
        me = this;

        $("#wallEntry_" + wallEntryId).each(function () {
            me.loadedEntryCount -= 1;
            $(this).hide();
        });

        // Start normal stream
        if (this.currentMode == 'single' || this.loadedEntryCount < 1) {
            this.showStream();
        }
    }


    /**
     * Shows the Stream
     */
    this.prependEntry = function (wallEntryId) {

        me = this;

        // Start normal stream
        if (this.currentMode == 'single' || this.loadedEntryCount == 0) {
            this.showStream();
            return;
        }

        url = this.urlSingle;
        wallEntryId = parseInt(wallEntryId);
        url = url.replace('fromEntryId', wallEntryId + 1);


        jQuery.getJSON(url, function (json) {
            me.loadedEntryCount += 1;

            streamDiv = $(me.baseDiv).find(".s2_streamContent");
            $(parseEntriesHtml(json)).prependTo($(streamDiv)).fadeIn('fast');

            me.onNewEntries();

            // In case of first post / hide message
            $(me.baseDiv).find(".emptyStreamMessage").hide();

        });

    }


    /**
     * Loads more Items
     */
    this.loadMore = function () {

        me = this;

        me.loadingBusyFlag = true;

        if (this.lastEntryLoaded)
            return;

        // Enable Loader 
        $(this.baseDiv).find(".streamLoader").show();

        // Build Reload URL
        url = this.urlReload;
        url = url.replace('lastEntryId', this.lastLoadedEntryId);
        url = url.replace('filter_placeholder', this.filters);
        url = url.replace('sort_placeholder', this.sorting);

        // Get New Item
        jQuery.getJSON(url, function (json) {

            if (json.counter == 0) {
                me.lastEntryLoaded = true;
                $(me.baseDiv).find(".streamLoader").hide();

            } else {

                me.loadedEntryCount += json.counter;

                streamDiv = $(me.baseDiv).find(".s2_streamContent");
                //$(json.output).appendTo(streamDiv).fadeIn('fast');
                $(streamDiv).append(parseEntriesHtml(json,streamDiv ));

                me.lastLoadedEntryId = json.lastEntryId;
                me.onNewEntries();

                $(me.baseDiv).find(".streamLoader").hide();
            }
            me.loadingBusyFlag = false;
        });
    }


    /**
     * Fired when new entries are shown
     */
    this.onNewEntries = function () {

        if (this.readOnly) {
            $('.wallReadOnlyHide').hide();
            $('.wallReadOnlyShow').show();
        } else {
            $('.wallReadOnlyShow').hide();
        }

    }


    /**
     *  Filter Handler & Sorting
     */
    this.showFilter = function () {

        $('.wallFilterPanel').show();

        me = this;

        // Handle Clicks on Filter "Checkboxes"
        $(".wallFilter").off();
        $(".wallFilter").click(function () {
            checkboxi = $(this).children("i");
            if (checkboxi.hasClass('fa-square-o')) {
                checkboxi.removeClass('fa-square-o');
                checkboxi.addClass('fa-check-square-o');
            } else {
                checkboxi.addClass('fa-square-o');
                checkboxi.removeClass('fa-check-square-o');
            }

            me.updateFilters();
        });

        // Handles clicks on sorting
        $(".wallSorting").off();
        $(".wallSorting").click(function () {
            newSortingMode = $(this).attr('id');

            // uncheck all sorting
            $(".wallSorting").each(function () {
                $(this).children("i").removeClass('fa-check-square-o');
                $(this).children("i").addClass('fa-square-o');
            });

            // check current sorting mode
            $("#" + newSortingMode).children("i").removeClass('fa-square-o');
            $("#" + newSortingMode).children("i").addClass('fa-check-square-o');

            // remove sorting id append
            newSortingMode = newSortingMode.replace('sorting_', '');

            // Switch sorting mode and reload stream
            me.sorting = newSortingMode;
            me.showStream();

        });

    }

    /**
     * Hide Filters when not necessary
     */
    this.hideFilter = function () {
        $('.wallFilterPanel').hide();
    }

    /**
     * Reads the current state of filter checkboxes and reloads the stream.
     */
    this.updateFilters = function () {
        me = this;

        this.filters = ""; // clear

        // Loop over all available filters
        $(".wallFilter").each(function () {

            checkboxi = $(this).children("i");

            // Is filter enabled?
            if (checkboxi.hasClass('fa-check-square-o')) {
                filterName = $(this).attr('id');

                // remove id addon filter_
                filterName = filterName.replace('filter_', '');

                me.filters += filterName + ",";

            }
        });

        // Restart Stream
        this.showStream();
    };

    /**
     * Reloads a given Wall Entry
     *
     * @returns {undefined}
     */
    this.reloadWallEntry = function (wallEntryId) {

        wallEntryId = parseInt(wallEntryId);

        url = this.urlSingle;
        url = url.replace('fromEntryId', wallEntryId + 1);

        // Load Entry
        jQuery.getJSON(url, function (json) {
            $("#wallEntry_" + wallEntryId).replaceWith(parseEntriesHtml(json));
            me.onNewEntries();
        });
    };


    // Start Clear per default on a new instance
    this.clear();


}
function installAutoLoader() {
    // Install autoscrolloer
    $(window).scroll(function () {
        if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            if (currentStream && currentStream.loadingBusyFlag == false) {
                currentStream.loadMore();
            }
        }
    });
}
