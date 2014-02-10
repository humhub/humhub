<?php
/**
 * This view represents the basic layout of an activity entry.
 *
 * @property ActivityWidget $this the widget which shows the activity
 * @property Activity $activity the current activity
 *
 * @package humhub.modules.activity
 * @since 0.5
 */

?>



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

                                            <?php echo $content; ?>

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





