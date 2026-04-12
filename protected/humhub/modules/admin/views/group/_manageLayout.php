<?php

use humhub\helpers\Html;
use humhub\modules\admin\widgets\GroupManagerMenu;
use humhub\modules\user\models\Group;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;

/* @var $group Group */
/* @var $content string */

?>
<div class="panel-body clearfix">
    <div class="float-end">
        <?= Button::back(['/admin/group/index'], Yii::t('AdminModule.base', 'Back to overview')) ?>
        <?php if ($group->is_admin_group) : ?>
            <br>
            <?= Badge::danger(Yii::t('AdminModule.base', 'Administrative group'))->cssClass('mt-2') ?>
        <?php endif; ?>
    </div>
    <?php if (!$group->isNewRecord) : ?>
        <h4><?= Yii::t('AdminModule.user', 'Manage group: {groupName}', ['groupName' => Html::encode($group->name)]) ?></h4>
    <?php else: ?>
        <h4><?= Yii::t('AdminModule.user', 'Add new group') ?></h4>
    <?php endif; ?>
</div>

<?php if (!$group->isNewRecord) : ?>
    <?= GroupManagerMenu::widget(['group' => $group]) ?>
<?php endif; ?>

<?= $content ?>
