<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\models\Space;
use humhub\modules\ui\mail\DefaultMailStyle;
use humhub\modules\user\models\User;
use yii\helpers\Html;

/* @var User $originator */
/* @var Space $space */
/* @var string $registrationUrl */
?>
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= $this->theme->variable('background-color-main', '#fff') ?>">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= $this->theme->variable('background-color-main', '#fff') ?>">


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
                                                        style=" background-color:<?= $this->theme->variable('background-color-main', '#fff') ?>; background-clip: padding-box; font-size:26px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; text-align:center; color:<?= $this->theme->variable('text-color-soft2', '#aeaeae') ?>; font-weight: 300; padding: 0 18px">

                                                        <span style="color: <?= $this->theme->variable('text-color-main', '#555') ?>; font-weight: 300">
                                                            <?= Yii::t('UserModule.base', 'You got an invite') ?>
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
               style="background-color: <?= $this->theme->variable('background-color-main', '#fff') ?>; border-radius: 0 0 4px 4px">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= $this->theme->variable('background-color-main', '#fff') ?>">

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

                                                    <td valign="top" align="center" style="padding-right:20px">
                                                        <!-- START: USER IMAGE -->
                                                        <a href="<?= $originator->createUrl('/user/profile', [], true) ?>">
                                                            <img
                                                                src="<?= $originator->getProfileImage()->getUrl('', true) ?>"
                                                                width="69"
                                                                alt=""
                                                                style="max-width:69px; display:block !important; border-radius: 4px;"
                                                                border="0" hspace="0" vspace="0"/>
                                                        </a>
                                                        <!-- END: USER IMAGE -->
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    <!--start space height -->
                                    <tr>
                                        <td height="15" class="col-underline"></td>
                                    </tr>
                                    <!--end space height -->

                                    <tr>
                                        <td valign="top" align="center">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                                   align="center">
                                                <tr>
                                                    <td style="font-size: 18px; line-height: 22px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; font-weight:300; text-align:center">
                                                        <span
                                                            style="color: <?= $this->theme->variable('text-color-highlight', '#555') ?>; font-weight: 300">
                                                            <a href="<?= $originator->createUrl('/user/profile', [], true) ?>"
                                                               style="text-decoration: none; color: <?= $this->theme->variable('text-color-highlight', '#555') ?>; font-weight: 300">
                                                                <!-- START: USER NAME -->
                                                                <?= Html::encode($originator->displayName) ?>
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
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= $this->theme->variable('background-color-main', '#fff') ?>">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= $this->theme->variable('background-color-main', '#fff') ?>">


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
                                                    <td style="font-size: 14px; line-height: 22px; padding-left: 50px; padding-right: 50px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:<?= $this->theme->variable('text-color-main', '#777') ?>; font-weight:300; text-align:center; ">

                                                        <!-- START: CONTENT -->
                                                        <?= Yii::t('UserModule.base', 'invited you to join {space} on {name}.', [
                                                            'space' => '<strong>' . Html::encode($space->name) . '</strong>',
                                                            'name' => Html::encode(Yii::$app->name),
                                                        ]) ?>
                                                        <br/>
                                                        <br/>
                                                        <?= Yii::t('UserModule.base', 'Register now and participate!') ?>
                                                        <br/>
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
                                                    <td width="auto" align="center" valign="middle" height="32"
                                                        style=" background-color:<?= $this->theme->variable('primary') ?>; border-radius:5px; background-clip: padding-box;font-size:14px; font-family: <?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; text-align:center;  color:<?= $this->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 600; padding: 5px 30px">

                                                        <span
                                                            style="color: <?= $this->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300">
                                                            <a href="<?php echo $registrationUrl ?>"
                                                               style="text-decoration: none; color: <?= $this->theme->variable('text-color-contrast', '#fff') ?>; font-weight: 300">
                                                                <strong><?php echo Yii::t('UserModule.base', 'Sign up now') ?></strong>
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
