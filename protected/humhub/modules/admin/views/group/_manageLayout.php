<?php

use humhub\libs\Html;
use humhub\modules\admin\widgets\GroupManagerMenu;
use humhub\modules\user\models\Group;
use humhub\widgets\Button;

/* @var $group Group */

?>
<div class="panel-body">
    <div class="pull-right">
        <?= Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))?>
    </div>

    <?php if (!$group->isNewRecord) : ?>
        <h4><?= Yii::t('AdminModule.user', 'Manage group: {groupName}', ['groupName' => Html::encode($group->name)]); ?></h4>
    <?php else: ?>
        <h4><?= Yii::t('AdminModule.user', 'Add new group'); ?></h4>
    <?php endif; ?>
</div>

<?php if (!$group->isNewRecord) : ?>
    <br />
<?php endif; ?>

<?php if (!$group->isNewRecord) : ?>
    <?php if ($group->is_admin_group) : ?>
        <div class="pull-right"><span class="label label-danger"><?= Yii::t('AdminModule.base', 'Administrative group'); ?></span>&nbsp;&nbsp;</div>
    <?php endif; ?>
    <?= GroupManagerMenu::widget(['group' => $group]); ?>
<?php endif; ?>

<?= $content; ?>
