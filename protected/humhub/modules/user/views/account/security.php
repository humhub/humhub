<?php

use yii\bootstrap\Html;
use yii\helpers\Url;
use humhub\modules\user\widgets\PermissionGridEditor;
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Security</strong> settings'); ?>
</div>
<div class="panel-body">
    <?php if ($multipleGroups) : ?>
        <div class="tab-menu">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($groups as $groupId => $groupTitle) : ?>
                    <li role="presentation" class="<?php if ($groupId == $group): ?>active<?php endif; ?>">
                        <a href="<?= Url::to(['security', 'groupId' => $groupId]); ?>" role="tab" ><?= Html::encode($groupTitle); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <br />
    <?php endif; ?>

    <div class="tab-content">
        <?= PermissionGridEditor::widget(['permissionManager' => $user->permissionManager, 'groupId' => $group]); ?>
    </div>
</div>