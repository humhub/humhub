<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $buttons array */
?>

<table width="100%">
    <tr>
        <td valign="top" width="auto" align="center">
            <table border="0" align="center" cellpadding="0" cellspacing="0">
                <?php $count = count($buttons) ?>
                <?php $index = 0 ?>
                <?php foreach($buttons as $button) : ?>
                    <?php if($count > 1 && $index++ !== 0) : ?>
                        <td width="2"></td>
                    <?php endif; ?>
                    <?= $button ?>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>