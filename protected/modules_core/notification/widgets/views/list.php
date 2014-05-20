<div class="btn-group">
    <a href="#" id="icon-notifications" data-toggle="dropdown">
        <i class="fa fa-bell"></i>
    </a>
    <span id="badge-notifications" style="display:none;" class="label label-danger label-notifications">1</span>

    <!-- container for ajax response -->
    <ul id="dropdown-notifications" class="dropdown-menu">
        <li class="dropdown-header">
            <div class="arrow"></div><?php echo Yii::t('base', 'Notifications'); ?>
            <div class="dropdown-header-link"><a
                    href="javascript:markNotificationsAsSeen();"><?php echo Yii::t('NotificationModule', 'Mark all as seen'); ?></a>
            </div>
        </li>
        <ul class="media-list"></ul>
        <li id="loader_notifications">
            <div class="loader"></div>
        </li>
    </ul>
</div>


<script type="text/javascript">

    // set niceScroll to notification list
    $("#dropdown-notifications ul.media-list").niceScroll({
        cursorwidth: "7",
        cursorborder:"",
        cursorcolor:"#555",
        cursoropacitymax:"0.2",
        railpadding:{top:0,right:3,left:0,bottom:0}
    });


    function markNotificationsAsSeen() {

        //$('#dropdown-notifications').css({display: 'block'});

        // call ajax request to mark all notifications as seen
        jQuery.ajax({
            'type': 'GET',
            'url': '<?php echo $this->createUrl('//notification/list/markAsSeen', array('ajax' => 1)); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function(html) {
                // hide notification badge at the top menu
                $('#badge-notifications').css('display', 'none');
            }});
    }

    $(document).ready(function () {

        // set the ID for the last loaded activity entry to 1
        var notificationLastLoadedEntryId = 0;

        // save if the last entries are already loaded
        var notificationLastEntryReached = false;

        // safe action url
        var _notificationUrl = '<?php echo $this->createUrl('//notification/list/index', array('from' => 'lastEntryId', 'ajax' => 1)); ?>';

        // Open the notification menu
        $('#icon-notifications').click(function () {

            // reset variables by dropdown reopening
            notificationLastLoadedEntryId = 0;
            notificationLastEntryReached = false;

            // remove all notification entries from dropdown
            $('#dropdown-notifications ul.media-list').find('li').remove();

            // checking if ajax loading is necessary or the last entries are already loaded
            if (notificationLastEntryReached == false) {

                // load notifications
                loadNotificationEntries();

            }


        })


        $('#dropdown-notifications ul.media-list').scroll(function () {

            // save height of the overflow container
            var _containerHeight = $("#dropdown-notifications ul.media-list").height();

            // save scroll height
            var _scrollHeight = $("#dropdown-notifications ul.media-list").prop("scrollHeight");

            // save current scrollbar position
            var _currentScrollPosition = $('#dropdown-notifications ul.media-list').scrollTop();

            // load more activites if current scroll position is near scroll height
            if (_currentScrollPosition >= (_scrollHeight - _containerHeight - 1)) {

                // checking if ajax loading is necessary or the last entries are already loaded
                if (notificationLastEntryReached == false) {

                    // load more notifications
                    loadNotificationEntries();

                }

            }

        });


        function loadNotificationEntries() {

            // replace placeholder name with the id from the last loaded entry
            var _modifiedNotificationUrl = _notificationUrl.replace('lastEntryId', notificationLastLoadedEntryId)

            // show loader
            $("#loader_notifications .loader").show();

            // send ajax request
            jQuery.getJSON(_modifiedNotificationUrl, function (json) {

                // hide loader
                $("#loader_notifications .loader").hide();

                // save id from the last entry for the next loading
                notificationLastLoadedEntryId = json.lastEntryId;

                if (json.counter < 6) {
                    // prevent the next ajax calls, if there are no more entries
                    notificationLastEntryReached = true;
                }

                // add new entries
                $("#dropdown-notifications ul.media-list").append(json.output);

                // format time
                $('span.time').timeago();


            });
        }


        // load number of new notifications at page loading
        getNotifications();

        // load number of new notifications in a loop
        setInterval(getNotifications, 60000);


        // load and show new count of notifications
        function getNotifications() {

            var $newNotifications = parseInt(0);

            // load data
            jQuery.getJSON("<?php echo $this->createUrl('//dashboard/dashboard/GetFrontEndInfo'); ?>", function (json) {

                // save numbers to variables
                $newNotifications = parseInt(json.newNotifications);

                // show or hide the badge for new notifications
                if ($newNotifications == 0) {
                    $('#badge-notifications').css('display', 'none');
                } else {
                    $('#badge-notifications').empty();
                    $('#badge-notifications').append($newNotifications);
                    $('#badge-notifications').fadeIn('fast');
                }

            })

        }


    })

</script>