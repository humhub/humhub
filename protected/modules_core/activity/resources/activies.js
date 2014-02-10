/*
 * http://www.webdeveloperjuice.com/2010/02/24/create-infinte-scroll-effect-using-jquery-with-demo/
 */


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


$(document).ready(function() {

    var activityLastLoadedEntryId = 0;

    $.fn.scrollLoad = function(options) {
        var defaults = {
            url: '',
            data: '',
            ScrollAfterHeight: 90,
            onload: function(data, itsMe) {
                alert(data);
            },
            getUrl: function(data, itsMe) {
                return '';
            },
            start: function(itsMe, x) {
            },
            continueWhile: function() {
                return true;
            },
            getData: function(itsMe) {
                return '';
            }
        };

        for (var eachProperty in defaults) {
            if (options[ eachProperty ]) {
                defaults[ eachProperty ] = options[ eachProperty ];
            }
        }

        return this.each(function() {


            this.scrolling = false;
            this.scrollPrev = this.onscroll ? this.onscroll : null;
            $(this).bind('scroll', function(e) {

                if (this.scrollPrev) {
                    this.scrollPrev();
                }

                if (this.scrolling)
                    return;
                if (Math.round($(this).prop('scrollTop') / ($(this).prop('scrollHeight') - $(this).prop('clientHeight')) * 100) > defaults.ScrollAfterHeight) {
                    $this = $(this);
                    defaults.start.call(this, $this);
                    this.scrolling = true;
                    jQuery.getJSON(defaults.getUrl.call(), function(json) {
                        $this[ 0 ].scrolling = false;
                        defaults.onload.call($this[ 0 ], json, $this[ 0 ]);
                        if (!defaults.continueWhile.call($this[ 0 ], json)) {
                            $this.unbind('scroll');
                        }
                    });

                }
            });
        });
    }

    // First Load Request
    jQuery.getJSON(activityStartUrl, function(json) {
        $("#activityLoader").hide();
        //$(json.output).appendTo('#activityContents').fadeIn('fast');
        $('#activityContents').append(json.output);
        activityLastLoadedEntryId = json.lastEntryId;

        if (json.counter == 0) {
            $("#activityEmpty").show();
            return;
        }

        // Install autoloader
        $('#activityStream').scrollLoad({
            ScrollAfterHeight: 75,
            getUrl: function( ) {
                url = activityReloadUrl;
                url = url.replace('lastEntryId', activityLastLoadedEntryId);
                return url;
            },
            start: function() {
                $("#activityLoader").show();
            },
            onload: function(json) {
                //$(json.output).appendTo('#activityContents').fadeIn('fast');
                $('#activityContents').append(json.output);
                activityLastLoadedEntryId = json.lastEntryId;

            },
            continueWhile: function(json) {
                if (json.counter == 0) {
                    $("#activityLoader").hide();
                    return false;
                }
                return true;
            }
        });

    });
});

