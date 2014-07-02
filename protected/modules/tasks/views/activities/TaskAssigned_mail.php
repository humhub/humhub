
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
                                                        <!-- START: USER IMAGE -->
                                                        <a href="<?php echo Yii::app()->createUrl('user/profile', array('guid' => $user->guid)); ?>">
                                                            <img
                                                                src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                                                                width="69"
                                                                alt="face1_69x69"
                                                                style="max-width:69px; display:block !important; border-radius: 4px;"
                                                                border="0" hspace="0" vspace="0"/>
                                                        </a>
                                                        <!-- END: USER IMAGE -->
                                                    </td>


                                                    <td valign="top">

                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                                               align="left">
                                                            <tr>
                                                                <td style="font-size: 18px; line-height: 22px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left;">
                                 <span style="color: #555555; font-weight: 300;">
                                   <a href="<?php echo Yii::app()->createUrl('user/profile', array('guid' => $user->guid)); ?>"
                                      style="text-decoration: none; color: #555555; font-weight: 300;">
                                       <!-- START: USER NAME -->
                                       <?php echo $user->displayName; ?>
                                       <!-- END: USER NAME -->
                                   </a>
                                 </span>
                                                                </td>
                                                            </tr>

                                                            <!--start space height -->
                                                            <tr>
                                                                <td height="10"></td>
                                                            </tr>
                                                            <!--end space height -->

                                                            <tr>
                                                                <td style="font-size: 13px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#a3a2a2; font-weight:300; text-align:left; ">
                                                                    <!-- START: CONTENT -->
                                                                    <?php echo Yii::t('ActivityModule.base', 'was assigned to the task:'); ?> <?php echo Helpers::truncateText($target->title, 25); ?> <?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?> in <strong><?php echo Helpers::truncateText($workspace->name, 25); ?></strong><?php endif; ?>
                                                                    &nbsp;
                                                                    <!-- END: CONTENT -->

                                                                    <!-- START: CONTENT LINK -->
                                                                    <span
                                                                        style="text-decoration: none; color: #7191a8;"><a
                                                                            href="<?php echo Yii::app()->createUrl('wall/perma/content', array('model'=>'Task', 'id'=>$target->id)); ?>"
                                                                            style="text-decoration: none; color: #7191a8; "><strong><?php echo Yii::t('ActivityModule.base', 'go to task'); ?></strong></a></span>
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



































