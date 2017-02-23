<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; ">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <!-- start button -->
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto" align="center" valign="middle" height="28"
                                                        style=" background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; background-clip: padding-box; font-size:26px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center; font-weight: 300; padding-left:18px; padding-right:18px; ">

                                                        <span style="color:  <?= Yii::$app->view->theme->variable('text-color-highlight', '#555') ?>; font-weight: 300;">
                                                            <?= Yii::t('UserModule.views_mails_UserInviteSelf', 'Welcome to %appName%', array('%appName%' => '<strong>' . Html::encode(Yii::$app->name) . '</strong>')); ?>
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
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; ">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           bgcolor="#ffffff" style="background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="center">


                                                <!--start space height -->
                                                <tr>
                                                    <td height="15"></td>
                                                </tr>
                                                <!--end space height -->

                                                <tr>
                                                    <td style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-main', '#777') ?>; font-weight:300; text-align:center; ">

                                                        <?php echo Yii::t('UserModule.views_mails_UserInviteSelf', 'Welcome to %appName%. Please click on the button below to proceed with your registration.', array('%appName%' => Html::encode(Yii::$app->name))); ?>

                                                    </td>
                                                </tr>

                                                <!--start space height -->
                                                <tr>
                                                    <td height="15"></td>
                                                </tr>
                                                <!--end space height -->


                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end text content -->

                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <!-- start button -->
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto" align="center" valign="middle" height="32"
                                                        style=" background-color:<?= $this->theme->variable('primary'); ?>;  border-radius:5px; background-clip: padding-box;font-size:14px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center;  color:#ffffff; font-weight: 600; padding-left:30px; padding-right:30px; padding-top: 5px; padding-bottom: 5px;">

                                                        <span style="color: <?= Yii::$app->view->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300;">
                                                            <a href="<?php echo Url::toRoute(['/user/registration', 'token' => $token], true); ?>"
                                                               style="text-decoration: none; color: <?= Yii::$app->view->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300;">
                                                                <strong><?php echo Yii::t('UserModule.views_mails_UserInviteSelf', 'Sign up'); ?></strong>
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
                            <td height="20"></td>
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
