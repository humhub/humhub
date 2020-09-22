<?php

use humhub\modules\user\widgets\PermisionGridModuleFilter;
use yii\bootstrap\Html;
use yii\helpers\Url;
use humhub\modules\user\widgets\PermissionGridEditor;

/** @var $multipleGroups bool **/
/** @var $group string **/
/** @var $user \humhub\modules\user\models\User  **/
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Security</strong> settings'); ?>
</div>
<div class="panel-body">

    <div class="panel-body">
        <p class="help-block"><?= Yii::t('UserModule.account', 'Here you can manage your account permissions for different user-types. To edit a permission, select the user-type you want to edit and change the drop-down value of the given permission.'); ?></p>
    </div>

    <div class="clearfix">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>

    <?php if ($multipleGroups) : ?>
        <div class="tab-menu permission-group-tabs">
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