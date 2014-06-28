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

                            <img id="user-account-image" class="img-rounded"
                                 src="<?php echo Yii::app()->user->model->getProfileImage()->getUrl(); ?>"
                                 height="32" width="32" alt="32x32" data-src="holder.js/32x32"
                                 style="width: 32px; height: 32px;"/>

                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?php echo $this->createUrl('//user/profile', array('guid' => Yii::app()->user->guid)); ?>"><i
                                        class="fa fa-user"></i> <?php echo Yii::t('UserModule.base', 'My profile'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $this->createUrl('//user/account/edit') ?>"><i
                                        class="fa fa-edit"></i> <?php echo Yii::t('UserModule.base', 'Account settings'); ?>
                                </a>
                            </li>

                            <?php if (Yii::app()->user->isAdmin()) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/index') ?>"><i
                                            class="fa fa-cogs"></i> <?php echo Yii::t('AdminModule.base', 'Administration'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <!-- if the current user has admin rights -->
                            <?php if (HSetting::Get('needApproval', 'authentication_internal') && (Yii::app()->user->isAdmin() || Yii::app()->user->canApproveUsers())) : ?>
                                <li>
                                    <a href="<?php echo $this->createUrl('//admin/approval') ?>"><i
                                            class="fa fa-check-circle"></i> <?php echo Yii::t('AdminModule.base', 'User Approvals'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>


                            <?php if (!isset(Yii::app()->session['ntlm']) || Yii::app()->session['ntlm'] == false) : ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?php echo $this->createUrl('//user/auth/logout') ?>"><i
                                            class="fa fa-sign-out"></i> <?php echo Yii::t('UserModule.base', 'Logout'); ?>
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

    // call this after every ajax loading
    $(document).ajaxComplete(function(event, xhr, settings) {

        // show Tooltips on elements inside the views, which have the class 'tt'
        $('.tt').tooltip({html: true});

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
</body>
</html>




