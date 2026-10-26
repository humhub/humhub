<?php

use humhub\components\View;
use humhub\modules\admin\widgets\GroupManagerMenu;
use humhub\modules\user\models\Group;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\ContentCard;

/* @var $this View */
/* @var $group Group */
/* @var $content string */
?>
<?php ContentCard::begin([
    'title' => $group->isNewRecord
        ? Yii::t('AdminModule.user', 'Add new group')
        : Yii::t('AdminModule.user', 'Manage group: {groupName}', ['groupName' => $group->name]),
    'badges' => $group->is_admin_group
        ? Badge::danger(Yii::t('AdminModule.base', 'Administrative group'))->asString()
        : '',
    'backUrl' => ['/admin/group/index'],
    'backLabel' => Yii::t('AdminModule.base', 'Back to overview'),
    'tabs' => $group->isNewRecord ? null : new GroupManagerMenu(['group' => $group]),
]) ?>
<?= $content ?>
<?php ContentCard::end() ?>
