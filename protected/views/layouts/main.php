<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- start: Meta -->
    <meta charset="utf-8">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <!-- end: Meta -->

    <!-- start: Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- end: Mobile Specific -->

    <?php $ver = HVersion::VERSION; ?>

    <!-- start: CSS -->
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/animate.min.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/bootstrap.min.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/datepicker.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/style.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/resources/font-awesome/css/font-awesome.min.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/bootstrap-wysihtml5.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    <link href="<?php echo Yii::app()->baseUrl; ?>/css/flatelements.css?ver=<?php echo $ver; ?>" rel="stylesheet">
    
    <!-- end: CSS -->


    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="<?php echo Yii::app()->baseUrl; ?>/js/html5shiv.js"></script>
    <link id="ie-style" href="<?php echo Yii::app()->baseUrl; ?>/css/ie.css" rel="stylesheet">
    <![endif]-->

    <!--[if IE 9]>
    <link id="ie9style" href="<?php echo Yii::app()->baseUrl; ?>/css/ie9.css" rel="stylesheet">
    <![endif]-->

    <!-- start: JavaScript -->
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/bootstrap.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/ekko-lightbox-modified.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/modernizr.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.cookie.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.highlight.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.autosize.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.timeago.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/locales/jquery.timeago.<?php echo Yii::app()->locale->getLanguageId(Yii::app()->language); ?>.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.knob.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/wysihtml5-0.3.0.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/bootstrap3-wysihtml5.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.nicescroll.min.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.flatelements.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.placeholder.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.iframe-transport.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.ui.widget.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.fileupload.js?ver=<?php echo $ver; ?>"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery.color-2.1.0.min.js?ver=<?php echo $ver; ?>"></script>
    
    <!-- start: render additional head (css and js files) -->
    <?php $this->renderPartial('//layouts/head'); ?>

    <!-- end: render additional head -->

    <!-- Global app functions -->
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/app.js?ver=<?php echo $ver; ?>"></script>
    <!-- end: JavaScript -->

    <!-- start: Favicon and Touch Icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo Yii::app()->baseUrl; ?>/ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>/ico/favicon.ico">
    <!-- end: Favicon and Touch Icons -->

    
    
</head>

<body>
    
<?php if (!Yii::app()->user->isGuest) { 

    $user = Yii::app()->user->getModel();
    if($user->getSetting("enable_html5_desktop_notifications", 'core', HSetting::Get('enable_html5_desktop_notifications', 'notification'))){?>
        <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/desktop-notify-min.js"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/desktop-notify-config.js"></script>
    <?php }?>
    
    <!-- start: first top navigation bar -->
    <div id="topbar-first" class="topbar">
        <div class="container">
            <div class="topbar-brand">
                <a class="navbar-brand hidden-xs"
                   href="<?php echo Yii::app()->createUrl('//'); ?>"><?php echo CHtml::encode(Yii::app()->name); ?></a>
            </div>

            <div class="topbar-actions pull-right">

                <ul class="nav">
                    <li class="dropdown account">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <div class="user-title pull-left hidden-xs">
                                <strong><?php echo CHtml::encode(Yii::app()->user->displayName); ?></strong><br/><span class="truncate"><?php echo CHtml::encode(Yii::app()->user->getModel()->profile->title); ?></span>
                            </div>

                            <img id="user-account-image" class="img-rounded"
                                 src="<?php echo Yii::app()->user->model->getProfileImage()->getUrl(); ?>"
                                 height="32" width="32" alt="32x32" data-src="holder.js/32x32"
                                 style="width: 32px; height: 32px;"/>

                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?php echo $this->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid)); ?>"><i
                                        class="fa fa-user"></i> <?php echo Yii::t('base', 'My profile'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $this->createUrl('//user/account/edit') ?>"><i
                                        class="fa fa-edit"></i> <?php echo Yii::t('base', 'Account settings'); ?>
                                </a>
                            </li>

                            <?php if (Yii::app()->user->isAdmin()) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/index') ?>"><i
                                            class="fa fa-cogs"></i> <?php echo Yii::t('base', 'Administration'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <!-- if the current user has admin rights -->
                            <?php if (HSetting::Get('needApproval', 'authentication_internal') && (Yii::app()->user->isAdmin() || Yii::app()->user->canApproveUsers())) : ?>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/approval') ?>"><i
                                            class="fa fa-check-circle"></i> <?php echo Yii::t('base', 'User Approvals'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <?php if (!isset(Yii::app()->session['ntlm']) || Yii::app()->session['ntlm'] == false) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//user/auth/logout') ?>"><i
                                            class="fa fa-sign-out"></i> <?php echo Yii::t('base', 'Logout'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>


            </div>

            <div class="notifications pull-right">

                <!-- global notifications dropdown -->
                <?php $this->widget('application.modules_core.notification.widgets.NotificationListWidget'); ?>

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

            <ul class="nav pull-right" id="search-menu-nav">
                <li class="dropdown">
                    <a href="#" id="search-menu" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-search"></i></a>
                    <ul class="dropdown-menu pull-right" id="search-menu-dropdown">
                        <?php $this->widget('application.widgets.TopMenuRightStackWidget', array(
                            'widgets' => array(
                                array('application.widgets.SearchMenuWidget', array())
                            )
                        )); ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- end: second top navigation bar -->

    <?php $this->widget('application.modules_core.tour.widgets.TourWidget', array()); ?>

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

        /* Ensures after hide modal content is removed. */
        $('#globalModal').on('hidden.bs.modal', function (e) {
            $(this).removeData('bs.modal');

            // just close modal and reset modal content to default (shows the loader)
            $(this).html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
        })

    });

    // call this after every ajax loading
    $(document).ajaxComplete(function(event, xhr, settings) {

        // show Tooltips on elements inside the views, which have the class 'tt'
        $('.tt').tooltip({
            html: true,
            container: 'body'
        });

        // show Popovers on elements inside the views, which have the class 'po'
        $('.po').popover({html: true});

        // activate placeholder text for older browsers (specially IE)
        $('input, textarea').placeholder();

    });

    $('#globalModal').on('shown.bs.modal', function (e) {
        // reduce the standard modal width
        $('.modal-dialog').css('width', '300px');
    })

</script>
<?php echo HSetting::GetText('trackingHtmlCode'); ?>
</body>
</html>
