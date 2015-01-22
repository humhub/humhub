<?php $this->beginContent('application.views.mail.template'); ?>

<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: #ffffff; ">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           bgcolor="#ffffff" style="background-color:#ffffff;">


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
                                                        style=" background-color:#ffffff; background-clip: padding-box; font-size:26px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; text-align:center;  color:#a3a2a2; font-weight: 300; padding-left:18px; padding-right:18px; ">

                                                                    <span style="color: #555555; font-weight: 300;">
                                                                        <?php echo Yii::t('base', 'Welcome to %appName%', array('%appName%' => '<strong>' . CHtml::encode(Yii::app()->name) . '</strong>')); ?>
                                                                        <br/>
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
               style="background-color: #ffffff; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           bgcolor="#ffffff" style="background-color:#ffffff;">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="left">


                                                <!--start space height -->
                                                <tr>
                                                    <td height="15"></td>
                                                </tr>
                                                <!--end space height -->

                                                <tr>
                                                    <td style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#777777; font-weight:300; text-align:left; ">

                                                        <?php echo $message; ?>

                                                    </td>
                                                </tr>


                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end text content -->


                                </table>
                            </td>
                        </tr>
                        <!-- end text content -->

                        <!--start space height -->
                        <tr>
                            <td height="15"></td>
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

<?php $this->endContent(); ?>
