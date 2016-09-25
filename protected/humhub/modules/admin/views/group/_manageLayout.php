<?php

use yii\bootstrap\Html;
use yii\helpers\Url;
?>
<div class="panel-body">
    <div class="pull-right">
        <?php echo Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.user', 'Back to overview'), Url::to(['index']), array('class' => 'btn btn-default')); ?>
    </div>   

    <?php if (!$group->isNewRecord) : ?>
        <h4><?php echo Yii::t('AdminModule.user', 'Manage group: {groupName}', ['groupName' => $group->name]); ?></h4>
    <?php else: ?>
        <h4><?php echo Yii::t('AdminModule.user', 'Add new group'); ?></h4>
    <?php endif; ?>
</div>

<br />

<?php if (!$group->isNewRecord) : ?>
    <?php if ($group->is_admin_group) : ?>
        <div class="pull-right"><span class="label label-danger"><?php echo Yii::t('AdminModule.group', 'Administrative group'); ?></span>&nbsp;&nbsp;</div>
    <?php endif; ?>
    <?= \humhub\modules\admin\widgets\GroupManagerMenu::widget(['group' => $group]); ?>
<?php endif; ?>

<?php echo $content; ?>



