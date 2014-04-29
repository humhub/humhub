<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <meta name="description" content="HumHub - Social Collaboration Software">
    <meta name="author" content="The HumHub Project">
    <meta name="keyword" content="HumHub, Social Intranet, Social Enterprise, Social Collaboration">
    <!-- end: Meta -->

    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- end: Mobile Specific -->

    <!-- start: render head (css and js files) -->
    <?php $this->renderPartial('//layouts/head'); ?>
    <!-- end: render head -->

</head>

<body>

<?php if (!Yii::app()->user->isGuest) { ?>


    <!-- start: first top navigation bar -->

    <div id="topbar-first" class="topbar">
        <div class="container">
            <div class="topbar-brand">
                <a class="navbar-brand hidden-xs"
                   href="<?php echo Yii::app()->createUrl('//'); ?>"><?php echo Yii::app()->name; ?></a>
            </div>

            <div class="topbar-actions pull-right">

                <ul class="nav">
                    <li class="dropdown account">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <div class="user-title pull-left hidden-xs">
                                <strong><?php echo Yii::app()->user->displayName; ?></strong><br/><span><?php echo Yii::app()->user->getModel()->profile->title; ?></span>
                            </div>

                            <img class="img-rounded"
                                 src="<?php echo Yii::app()->user->model->getProfileImage()->getUrl(); ?>"
                                 height="32" width="32" alt="32x32" data-src="holder.js/32x32"
                                 style="width: 32px; height: 32px;"/>

                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?php echo $this->createUrl('//user/profile', array('guid' => Yii::app()->user->guid)); ?>"><i
                                        class="icon-user"></i> <?php echo Yii::t('UserModule.base', 'My profile'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $this->createUrl('//user/account/edit') ?>"><i
                                        class="icon-edit"></i> <?php echo Yii::t('UserModule.base', 'Account settings'); ?>
                                </a>
                            </li>

                            <?php if (Yii::app()->user->isAdmin()) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/index') ?>"><i
                                            class="icon-cogs"></i> <?php echo Yii::t('AdminModule.base', 'Administration'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <!-- if the current user has admin rights -->
                            <?php if (HSetting::Get('needApproval', 'authentication_internal') && (Yii::app()->user->isAdmin() || Yii::app()->user->canApproveUsers())) : ?>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/approval') ?>"><i
                                            class="icon-check-sign"></i> <?php echo Yii::t('AdminModule.base', 'User Approvals'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <?php if (!isset(Yii::app()->session['ntlm']) || Yii::app()->session['ntlm'] == false) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//user/auth/logout') ?>"><i
                                            class="icon-signout"></i> <?php echo Yii::t('UserModule.base', 'Logout'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>


            </div>

            <div class="notifications pull-right">

                <!-- global notifications dropdown -->
                <div class="btn-group">
                    <a href="#" id="icon-notifications" data-toggle="dropdown">
                        <i class="icon-bell-alt"></i>
                    </a>
                    <span id="badge-notifications" style="display:none;" class="label label-danger label-notifications">1</span>

                    <!-- container for ajax response -->
                    <ul id="dropdown-notifications" class="dropdown-menu"></ul>
                </div>

                <!-- Notification addon widget for modules -->
                <?php $this->widget('application.widgets.NotificationAddonWidget', array('widgets' => array())); ?>

            </div>

        </div>

    </div>
    <!-- end: first top navigation bar -->


    <!-- start: second top navigation bar -->
    <div id="topbar-second" class="topbar">
        <div class="container">
            <ul class="nav ">
                <!-- load space chooser widget -->
                <?php $this->widget('application.modules_core.space.widgets.SpaceChooserWidget', array()); ?>

                <!-- load navigation from widget -->
                <?php $this->widget('application.widgets.TopMenuWidget', array()); ?>
            </ul>

            <ul class="nav pull-right">
                <li class="dropdown">
                    <a href="#" id="search-menu" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-search"></i></a>
                    <ul class="dropdown-menu pull-right" id="search-menu-dropdown">

                        <!-- load search menu widget -->
                        <?php $this->widget('application.widgets.SearchMenuWidget', array()); ?>

                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- end: second top navigation bar -->


<?php } ?>


<!-- start: show content (and check, if exists a sublayout -->
<?php if (isset($this->subLayout) && $this->subLayout != "") : ?>
    <?php echo $this->renderPartial($this->subLayout, array('content' => $content)); ?>
<?php else: ?>
    <?php echo $content; ?>
<?php endif; ?>
<!-- end: show content -->


<!-- start: Modal (every lightbox will/should use this construct to show content)-->
<div class="modal" id="globalModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</div>
<!-- end: Modal -->


<script type="text/javascript">

    // Replace the standard checkbox and radio buttons
    $('body').find(':checkbox, :radio').flatelements();

    $(document).ready(function () {



        // Open the notification menu
        $('#icon-notifications').click(function () {

            // remove all <li> entries from dropdown
            $('#dropdown-notifications').find('li').remove();

            // append title and loader to dropdown
            $('#dropdown-notifications').append('<li class="dropdown-header"><div class="arrow"></div><?php echo Yii::t('base', 'Notifications'); ?></li><li id="loader_notifications"><div class="loader"></div></li>');

            // load newest notifications
            $.ajax({
                'type': 'GET',
                'url': '<?php echo $this->createUrl('//notification/list', array('ajax' => 1)); ?>',
                'cache': false,
                'data': jQuery(this).parents("form").serialize(),
                'success': function (html) {
                    $("#loader_notifications").replaceWith(html)
                }});

        })


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


        // show Tooltips on elements inside the views, which have the class 'tt'
        $('.tt').tooltip({html: true});

        // show Popovers on elements inside the views, which have the class 'po'
        $('.po').popover({html: true});


        /* Ensures after hide modal content is removed. */
        $('#globalModal').on('hidden.bs.modal', function (e) {
            $(this).removeData('bs.modal');
            $(this).html("");
        })

    });

    // set niceScroll to body element
    $("body").niceScroll({
        cursorwidth: "7",
        cursorborder:"",
        cursorcolor:"#555",
        cursoropacitymax:"0.2",
        railpadding:{top:120,right:3,left:0,bottom:20}
    });

</script>


</body>
</html>




