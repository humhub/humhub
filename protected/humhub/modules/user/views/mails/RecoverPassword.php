<?php

use yii\helpers\Html;

?>
<tr>
    <td align="center" valign="top"   class="fix-box">

        <!-- start  container width 600px -->
        <table width="600"  align="center" border="0" cellspacing="0" cellpadding="0" class="container"  style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; ">

            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540"  align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"  style="background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>;">

                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" >
                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <!-- start button -->
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto"  align="center" valign="middle" height="28" style=" background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; background-clip: padding-box; font-size:26px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center; font-weight: 300; padding-left:18px; padding-right:18px; ">
                                                        <span style="color: <?= Yii::$app->view->theme->variable('text-color-highlight', '#555') ?>; font-weight: 300;">
                                                            <?= Yii::t('UserModule.views_mails_RecoverPassword', '<strong>Password</strong> recovery'); ?>
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
    <td align="center" valign="top"   class="fix-box">

        <!-- start  container width 600px -->
        <table width="600"  align="center" border="0" cellspacing="0" cellpadding="0" class="container"  style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540"  align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color:<?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" >


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="left" >


                                                <!--start space height -->
                                                <tr>
                                                    <td height="30" ></td>
                                                </tr>
                                                <!--end space height -->

                                                <tr>
                                                    <td  style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-main', '#777') ?>; font-weight:300; text-align:left; ">

                                                        <?php echo Yii::t('UserModule.views_mails_RecoverPassword', 'Hello {displayName}', array('{displayName}' => Html::encode($user->displayName))); ?>
                                                        <br><br>
                                                        <?php echo Yii::t('UserModule.views_mails_RecoverPassword', 'Please use the following link within the next day to reset your password.'); ?>
                                                        <br>
                                                        <?php echo Yii::t('UserModule.views_mails_RecoverPassword', "If you don't use this link within 24 hours, it will expire."); ?>
                                                        <br>

                                                    </td>
                                                </tr>

                                                <!--start space height -->
                                                <tr>
                                                    <td height="30" ></td>
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
                                                    <td width="auto"  align="center" valign="middle" height="32" style=" background-color:<?= $this->theme->variable('primary'); ?>;  border-radius:5px; background-clip: padding-box;font-size:14px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center;  color:#ffffff; font-weight: 600; padding-left:30px; padding-right:30px; padding-top: 5px; padding-bottom: 5px;">

                                                        <span style="color: <?= Yii::$app->view->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300;">
                                                            <a href="<?= $linkPasswordReset; ?>" style="text-decoration: none; color: <?= Yii::$app->view->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300;">
                                                                <strong><?= Yii::t('UserModule.views_mails_RecoverPassword', 'Reset Password'); ?></strong>
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
