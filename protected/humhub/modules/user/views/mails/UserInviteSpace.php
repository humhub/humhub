<?php


use yii\helpers\Html;

?>
<tr>
    <td align="center" valign="top"   class="fix-box">

        <!-- start  container width 600px -->
        <table width="600"  align="center" border="0" cellspacing="0" cellpadding="0" class="container"  style="background-color: #ffffff; ">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540"  align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" bgcolor="#ffffff" style="background-color:#ffffff;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" >
                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <!-- start button -->
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto"  align="center" valign="middle" height="28" style=" background-color:#ffffff; background-clip: padding-box; font-size:26px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center;  color:#a3a2a2; font-weight: 300; padding-left:18px; padding-right:18px; ">

                                                        <span style="color: #555555; font-weight: 300;">
                                                            <?php echo Yii::t('UserModule.views_mails_UserInviteSpace', 'You got an invite'); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                            <!-- end button -->
                                        </td>
                                    </tr>



                                </table>
                            </td>
                        </tr>
                        <!-- end text content -->


                    </table>
                    <!-- end  container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>

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
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">

                                    <!--start space height -->
                                    <tr>
                                        <td height="20"></td>
                                    </tr>
                                    <!--end space height -->


                                    <!-- start content top-->
                                    <tr>
                                        <td valign="top" align="center">

                                            <table border="0" cellspacing="0" cellpadding="0" align="center">
                                                <tr>

                                                    <td valign="top" align="center" style="padding-right:20px;">
                                                        <!-- START: USER IMAGE -->
                                                        <a href="<?php echo $originator->createUrl('/user/profile', [], true); ?>">
                                                            <img
                                                                src="<?php echo $originator->getProfileImage()->getUrl("", true); ?>"
                                                                width="69"
                                                                alt=""
                                                                style="max-width:69px; display:block !important; border-radius: 4px;"
                                                                border="0" hspace="0" vspace="0"/>
                                                        </a>
                                                        <!-- END: USER IMAGE -->
                                                    </td>
                                                </tr>
                                            </table>

                                            <!--start space height -->
                                    <tr>
                                        <td height="15" class="col-underline"></td>
                                    </tr>
                                    <!--end space height -->

                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                           align="center">
                                        <tr>
                                            <td style="font-size: 18px; line-height: 22px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:center;">
                                                <span style="color: #555555; font-weight: 300;">
                                                    <a href="<?php echo $originator->createUrl('/user/profile', [], true); ?>"
                                                       style="text-decoration: none; color: #555555; font-weight: 300;">
                                                        <!-- START: USER NAME -->
                                                        <?php echo Html::encode($originator->displayName); ?>
                                                        <!-- END: USER NAME -->
                                                    </a>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                            </td>
                        </tr>
                        <!-- end  content top-->


                        <!--start space height -->
                        <tr>
                            <td height="5" class="col-underline"></td>
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

<tr>
    <td align="center" valign="top"   class="fix-box">

        <!-- start  container width 600px -->
        <table width="600"  align="center" border="0" cellspacing="0" cellpadding="0" class="container"  style="background-color: #ffffff; ">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540"  align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" bgcolor="#ffffff" style="background-color:#ffffff;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" >


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="center" >


                                                <!--start space height -->
                                                <tr>
                                                    <td height="15" ></td>
                                                </tr>
                                                <!--end space height -->

                                                <tr>
                                                    <td  style="font-size: 14px; line-height: 22px; padding-left: 50px; padding-right: 50px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#777777; font-weight:300; text-align:center; ">

                                                        <!-- START: CONTENT -->
                                                        <?php echo Yii::t('UserModule.views_mails_UserInviteSpace', 'invited you to join {space} on {name}.', ['space' => '<strong>' . Html::encode($space->name) . '</strong>', 'name' => Html::encode(Yii::$app->name)]); ?>
                                                        <br />
                                                        <br />
                                                        <?php echo Yii::t('UserModule.views_mails_UserInviteSpace', 'Register now and participate!'); ?><br/>
                                                        &nbsp;
                                                        <!-- END: CONTENT -->
                                                    </td>
                                                </tr>

                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end text content -->

                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <!-- start button -->
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto"  align="center" valign="middle" height="32" style=" background-color:<?php echo Yii::$app->settings->get('colorPrimary'); ?>;  border-radius:5px; background-clip: padding-box;font-size:14px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center;  color:#ffffff; font-weight: 600; padding-left:30px; padding-right:30px; padding-top: 5px; padding-bottom: 5px;">

                                                        <span style="color: #ffffff; font-weight: 300;">
                                                            <a href="<?php echo $registrationUrl; ?>" style="text-decoration: none; color: #ffffff; font-weight: 300;">
                                                                <strong><?php echo Yii::t('UserModule.views_mails_UserInviteSpace', 'Sign up now'); ?></strong>
                                                            </a>
                                                        </span>
                                                    </td>

                                                </tr>
                                            </table>
                                            <!-- end button -->
                                        </td>

                                    </tr>

                                </table>
                            </td>
                        </tr>
                        <!-- end text content -->

                        <!--start space height -->
                        <tr>
                            <td height="20" ></td>
                        </tr>
                        <!--end space height -->


                    </table>
                    <!-- end  container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>
