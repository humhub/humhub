<ul class="nav nav-pills preferences">
    <li class="dropdown ">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>
        <ul class="dropdown-menu pull-right">
            <li><a href="javascript:togglePanelUp('<?php echo $id; ?>');" class="panel-collapse"><i
                        class="fa fa-minus-square"></i> <?php echo Yii::t('base', 'Collapse'); ?></a></li>
            <li><a href="javascript:togglePanelDown('<?php echo $id; ?>');" class="panel-expand" style="display:none;"><i
                        class="fa fa-plus-square"></i> <?php echo Yii::t('base', 'Expand'); ?></a></li>

<!--                <li class="divider"></li>
                <li><a href="#"><i class="fa fa-pencil"></i> Edit tags </a></li>-->
            <?php $this->widget('application.widgets.PanelMenuEntryWidget', array()); ?>
        </ul>
    </li>
</ul>

<script type="text/javascript">

    $( document ).ready(function() {

        // check and set panel state from cookie
        checkPanelMenuCookie('<?php echo $id; ?>');
    });


</script>