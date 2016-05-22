<?php
use yii\helpers\Url;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', '<strong>Security</strong> settings'); ?>
</div>

<div class="tab-menu">
    <ul class="nav nav-tabs" role="tablist" style="margin-bottom:0px;">
        <?php foreach ($groups as $groupId => $groupTitle) : ?>
            <li role="presentation" class="<?php if ($groupId == $group): ?>active<?php endif; ?>">
                <a href="<?= Url::to(['security', 'groupId' => $groupId]); ?>" role="tab" ><?php echo $groupTitle; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="panel-body" style="padding-top:0px;">
    <div class="tab-content">
        <?= \humhub\modules\user\widgets\PermissionGridEditor::widget(['permissionManager' => $user->permissionManager, 'groupId' => $group]); ?>
    </div>
</div>
