<!-- START NOTIFICATION/ACTIVITY -->
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" bgcolor="#ffffff"
               style="background-color: #ffffff; border-bottom-left-radius: 4px; border-bottom-left-radius: 4px;">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           bgcolor="#ffffff" style="background-color:#ffffff;">

                        <!-- start image and content -->
                        <tr>
                            <td valign="top" width="100%">

                                <!-- start content left -->
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">

                                    <!--start space height -->
                                    <tr>
                                        <td height="20"></td>
                                    </tr>
                                    <!--end space height -->


                                    <!-- start content top-->
                                    <tr>
                                        <td valign="top" align="left">

                                            <table border="0" cellspacing="0" cellpadding="0" align="left">
                                                <tr>

                                                    <td valign="top" align="left" style="padding-right:20px;">
                                                        
                                                        <!-- check if variable exists and is true -->
                                                        <?php if ($notification->getCreator() !== null && empty($hideUserImage)): ?>
                                                            <!-- START: USER IMAGE -->
                                                            <a href="<?php echo Yii::app()->createAbsoluteUrl('user/profile', array('guid' => $notification->getCreator()->guid)); ?>">
                                                                <img
                                                                    src="<?php echo $notification->getCreator()->getProfileImage()->getUrl(); ?>"
                                                                    width="50"
                                                                    alt=""
                                                                    style="max-width:50px; display:block !important; border-radius: 4px;"
                                                                    border="0" hspace="0" vspace="0"/>
                                                            </a>
                                                            <!-- END: USER IMAGE -->
                                                        <?php endif; ?>
                                                        
                                                    </td>


                                                    <td valign="top">

                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                                               align="left">

                                                            <tr>
                                                                <td style="font-size: 13px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left; ">

                                                                    <?php echo $content; ?>

                                                                    <!-- check if variable exists and is true -->
                                                                    <?php if (!empty($showSpace)) : ?>

                                                                        <!-- check if variable is true -->
                                                                        <?php if ($showSpace == true) : ?>

                                                                            <!-- check if activity object has a space -->
                                                                            <?php if ($notification->space != null): ?>
                                                                                (<?php echo Yii::t('NotificationModule.views_notificationLayoutMail', 'via'); ?>
                                                                                <a href="<?php echo Yii::app()->createUrl('space/space', array('sguid' => $notification->space->guid)); ?>"
                                                                                   style="text-decoration: none; color: #555555;">
                                                                                    <?php echo CHtml::encode($notification->space->name); ?>
                                                                                </a>)
                                                                            <?php endif; ?>

                                                                        <?php endif; ?>

                                                                    <?php endif; ?>

                                                                    <!-- START: CONTENT LINK -->
                                                                    <span
                                                                        style="text-decoration: none; color: #7191a8;"> - <a
                                                                            href="<?php echo $notification->getUrl(); ?>"
                                                                            style="text-decoration: none; color: #7191a8; "><?php echo Yii::t('NotificationModule.views_notificationLayoutMail', 'see online'); ?></a></span>
                                                                    <!-- END: CONTENT LINK -->

                                                                </td>
                                                            </tr>

                                                        </table>

                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end  content top-->


                                    <!--start space height -->
                                    <tr>
                                        <td height="15" class="col-underline"></td>
                                    </tr>
                                    <!--end space height -->


                                </table>
                                <!-- end content left -->


                            </td>
                        </tr>
                        <!-- end image and content -->

                    </table>
                    <!-- end  container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>
<!-- END NOTIFICATION/ACTIVITY -->