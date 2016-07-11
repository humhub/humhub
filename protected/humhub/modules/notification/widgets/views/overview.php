<?php

use yii\helpers\Url;
?>
<div class="btn-group">
    <a href="#" id="icon-notifications" data-toggle="dropdown">
        <i class="fa fa-bell"></i>
    </a>
    <span id="badge-notifications" style="display:none;" class="label label-danger label-notifications">1</span>

    <!-- container for ajax response -->
    <ul id="dropdown-notifications" class="dropdown-menu">
        <li class="dropdown-header">
            <div class="arrow"></div><?php echo Yii::t('NotificationModule.widgets_views_list', 'Notifications'); ?>
            <div class="dropdown-header-link"><a id="mark-seen-link"
                                                 href="javascript:markNotificationsAsSeen();"><?php echo Yii::t('NotificationModule.widgets_views_list', 'Mark all as seen'); ?></a>
            </div>
        </li>
        <ul class="media-list"></ul>
        <li id="loader_notifications">
            <?php echo \humhub\widgets\LoaderWidget::widget(); ?>
        </li>
        <li>
            <div class="dropdown-footer">
                <a class="btn btn-default col-md-12" href="<?= Url::to(['/notification/overview']); ?>">
                    <?php echo Yii::t('NotificationModule.widgets_views_list', 'Show all notifications'); ?>
                </a>
            </div>
        </li>
    </ul>
</div>


<script type="text/javascript">

    // set niceScroll to notification list
    $("#dropdown-notifications ul.media-list").niceScroll({
        cursorwidth: "7",
        cursorborder: "",
        cursorcolor: "#555",
        cursoropacitymax: "0.2",
        railpadding: {top: 0, right: 3, left: 0, bottom: 0}
    });


    function markNotificationsAsSeen() {
        // call ajax request to mark all notifications as seen
        jQuery.ajax({
            'type': 'GET',
            'url': '<?php echo Url::to(['/notification/list/mark-as-seen', 'ajax' => 1]); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (html) {
                // hide notification badge at the top menu
                $('#badge-notifications').css('display', 'none');
                $('#mark-seen-link').css('display', 'none');

                // remove notification count from page title
                var pageTitle = $('title').text().replace(/\(.+?\)/g, '');
                $('title').text(pageTitle);

            }});
    }

    var originalTitle;

    $(document).ready(function () {

        originalTitle = document.title;

        // set the ID for the last loaded activity entry to 1
        var notificationLastLoadedEntryId = 0;

        // save if the last entries are already loaded
        var notificationLastEntryReached = false;

        // safe action url
        var _notificationUrl = '<?php echo Url::to(['/notification/list/index', 'from' => 'lastEntryId', 'ajax' => 1]); ?>';

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

        var notification_placeholder = "<?php echo Yii::t('NotificationModule.widgets_views_list', 'There are no notifications yet.') ?>"


        function loadNotificationEntries() {

            // replace placeholder name with the id from the last loaded entry
            var _modifiedNotificationUrl = _notificationUrl.replace('lastEntryId', notificationLastLoadedEntryId)

            // show loader
            $("#loader_notifications .loader").show();

            // send ajax request
            jQuery.getJSON(_modifiedNotificationUrl, function (json) {

                // hide loader
                $("#loader_notifications .loader").hide();

                if (json.counter == 0) {
                    $("#dropdown-notifications ul.media-list").append('<li class="placeholder">' + notification_placeholder + '</li>');
                    notificationLastEntryReached = true;
                } else {

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
                }
            });
        }

        /**
         * Regulary fetch new notifications
         */
        reloadNotificationInterval = <?= $updateInterval * 1000; ?>;
        setInterval(function () {
            jQuery.getJSON("<?php echo Url::to(['/notification/list/get-update-json']); ?>", function (json) {
                handleJsonUpdate(json);
            });
        }, reloadNotificationInterval);

        handleJsonUpdate(<?php echo \yii\helpers\Json::encode($update); ?>);

    });


    /**
     * Handles JSON Update
     * 
     * @param String json
     */
    function handleJsonUpdate(json) {
        $newNotifications = parseInt(json.newNotifications);

        // show or hide the badge for new notifications
        if ($newNotifications == 0) {
            document.title = originalTitle;
            $('#badge-notifications').css('display', 'none');
            $('#mark-seen-link').css('display', 'none');
            $('#icon-notifications .fa').removeClass("animated swing");
        } else {
            document.title = '(' + $newNotifications + ') ' + originalTitle;
            $('#badge-notifications').empty();
            $('#badge-notifications').append($newNotifications);
            $('#mark-seen-link').css('display', 'inline');
            $('#badge-notifications').fadeIn('fast');
            $('#icon-notifications .fa').addClass("animated swing");

            var $notifications = json.notifications;
            for (var i = 0; i < $notifications.length; i++) {
                notify.createNotification("Notification", {body: $("<span />", {html: $notifications[i]}).text(), icon: "ico/alert.ico"})
            }
        }

    }

</script>