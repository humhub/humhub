<?php

use humhub\components\View;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\SecurityTabMenu;
use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use humhub\widgets\ContentCard;

/* @var $this View */
/* @var $space Space */
/* @var $groups array */
/* @var $groupId string */
?>
<?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
    'title' => Yii::t('SpaceModule.manage', 'Security'),
    'tabs' => new SecurityTabMenu(['space' => $space]),
]) ?>
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.base', 'Permissions'),
    'description' => Yii::t('SpaceModule.manage', 'Permissions are assigned to different user-roles. To edit a permission, select the user-role you want to edit and change the drop-down value of the given permission.'),
]) ?>
<div class="clearfix">
    <div class="float-end me-4 mb-2">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>
</div>

<ul id="tabs" class="nav nav-tabs tab-sub-menu permission-group-tabs">
    <?php foreach ($groups as $currentGroupId => $groupLabel) : ?>
        <li class="nav-item">
            <a class="nav-link<?= ($groupId === $currentGroupId) ? ' active' : '' ?>" href="<?= $space->createUrl('permissions', ['groupId' => $currentGroupId]) ?>"><?= $groupLabel ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<?= PermissionGridEditor::widget(['permissionManager' => $space->permissionManager, 'groupId' => $groupId]); ?>
<?php ContentCard::end() ?>
<?php $this->endContent() ?>
