<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_user_add', '<strong>Add</strong> user'); ?></div>
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li><a
                    href="<?php echo $this->createUrl('index'); ?>"><?php echo Yii::t('AdminModule.views_user_index', 'Overview'); ?></a>
            </li>
            <li class="active">
                <a href="<?php echo $this->createUrl('add'); ?>"><?php echo Yii::t('AdminModule.views_user_index', 'Add new user'); ?></a>
            </li>
        </ul>
        <p />
        <?php echo $form; ?>

    </div>
</div>