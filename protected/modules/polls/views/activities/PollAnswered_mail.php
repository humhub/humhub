


<tr>
    <td align="center" valign="top">
        <!-- CENTERING TABLE // -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
            <tr>
                <td align="center" valign="top">
                    <!-- FLEXIBLE CONTAINER // -->
                    <table border="0" cellpadding="0" cellspacing="0" width="600" class="flexibleContainer">
                        <tbody>
                        <tr>
                            <td valign="top" width="600" class="flexibleContainerCell">

                                <!-- CONTENT TABLE // -->
                                <table align="Left" border="0" cellpadding="0" cellspacing="0" width="60"
                                       class="flexibleContainer">
                                    <tbody>
                                    <tr>
                                        <td align="Left" valign="top" class="imageContent">
                                            <a href="<?php echo $user->getUrl(); ?>">
                                                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="50"
                                                     class="flexibleImage">
                                            </a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <!-- // CONTENT TABLE -->


                                <!-- CONTENT TABLE // -->
                                <table align="Right" border="0" cellpadding="0" cellspacing="0" width="480"
                                       class="flexibleContainer">
                                    <tbody>
                                    <tr>
                                        <td valign="top" class="textContent">
                                            <strong><?php echo $user->displayName; ?></strong> <?php echo Yii::t('PollsModule.base', 'answered question'); ?>
                                            <br><br><?php echo Helpers::truncateText($target->question, 25); ?><?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?> in
                                                <strong><?php echo Helpers::truncateText($workspace->name, 25); ?></strong><?php endif; ?>
                                            <br/>
                                            <br>
                                            <a href="<?php echo Yii::app()->createUrl('wall/perma/content', array('model' => 'Poll', 'id' => $target->id)); ?>"><?php echo Yii::t('PollsModule.base', 'Go to poll...'); ?></a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <!-- // CONTENT TABLE -->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-- // FLEXIBLE CONTAINER -->
                </td>
            </tr>
            </tbody>
        </table>
        <!-- // CENTERING TABLE -->
    </td>
</tr>









