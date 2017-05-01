<?php
$this->registerJsFile("@web-static/js/panelMenu.js", ['position' => yii\web\View::POS_BEGIN]);
?>
<ul class="nav nav-pills preferences">
    <li class="dropdown ">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-label="<?= Yii::t('base', 'Toggle panel menu'); ?>" aria-haspopup="true"><i class="fa fa-angle-down"></i></a>
        <ul class="dropdown-menu pull-right">
            <li>
                <a href="javascript:togglePanelUp('<?= $id; ?>');" class="panel-collapse">
                    <i class="fa fa-minus-square"></i> <?= Yii::t('base', 'Collapse'); ?>
                </a>
            </li>
            <li>
                <a href="javascript:togglePanelDown('<?= $id; ?>');" class="panel-expand" style="display:none;">
                    <i class="fa fa-plus-square"></i> <?= Yii::t('base', 'Expand'); ?>
                </a>
            </li>
            <?php
            echo $this->context->extraMenus;
            ?>
        </ul>
    </li>
</ul>

<script type = "text/javascript">

    $(document).ready(function () {

        // check and set panel state from cookie
        checkPanelMenuCookie('<?php echo $this->context->id; ?>');
    });


</script>