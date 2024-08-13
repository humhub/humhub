<?php if(!empty($headline)) :?>
<tr>
    <td align="center" valign="top"  class="fix-box">
        <!-- start container width 600px -->
        <table width="600"  align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>; border-top-left-radius: 4px; border-top-right-radius: 4px;">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560"  align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: <?= Yii::$app->view->theme->variable('background-color-main', '#fff') ?>">

                        <!-- start image content -->
                        <tr>
                            <td valign="top" width="100%">

                                <!-- start content left -->
                                <table width="270" border="0" cellspacing="0" cellpadding="0" align="left" class="full-width"  >


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <?= humhub\widgets\mails\MailHeadline::widget(['text' => $headline]); ?>
                                        </td>
                                    </tr>
                                   
                                    <!-- end text content -->
                                </table>
                                <!-- end content left -->
                            </td>
                        </tr>
                        <!-- end image content -->

                    </table>
                    <!-- end container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>
 <?php endif; ?>
<!-- START NOTIFICATION CONTENT-->
<?= $content ?>
<!-- END NOTIFICATION CONTENT-->