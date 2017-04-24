<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
 
$this->registerJsFile("@web-static/js/panelMenu.js", ['position' => yii\web\View::POS_BEGIN]);
?>
<ul class="nav nav-pills preferences">
    <li class="dropdown ">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>
        <ul class="dropdown-menu pull-right">
            <li><a href="javascript:togglePanelUp('<?= $id; ?>');" class="panel-collapse"><i
                        class="fa fa-minus-square"></i> <?= Yii::t('base', 'Collapse'); ?></a></li>
            <li><a href="javascript:togglePanelDown('<?= $id; ?>');" class="panel-expand" style="display:none;"><i
                        class="fa fa-plus-square"></i> <?= Yii::t('base', 'Expand'); ?></a></li>

            <?php
            echo $this->context->extraMenus;
            ?>
        </ul>
    </li>
</ul>

<script>

    $(document).ready(function () {

        // check and set panel state from cookie
        checkPanelMenuCookie('<?= $this->context->id; ?>');
    });


</script>