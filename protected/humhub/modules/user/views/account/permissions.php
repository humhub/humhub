<?php

use humhub\modules\user\widgets\PermisionGridModuleFilter;
use yii\bootstrap\Html;
use yii\helpers\Url;
use humhub\modules\user\widgets\PermissionGridEditor;

/** @var $multipleGroups bool **/
/** @var $group string **/
/** @var $groups string[] **/
/** @var $user \humhub\modules\user\models\User  **/
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Permissions</strong>'); ?>
</div>
<div class="panel-body">

    <div class="panel-body">
        <p class="help-block"><?= Yii::t('UserModule.account', 'These settings allow you to determine which permissions you want to grant visitors of your own individual profile. Each user can freely adjust the settings for his or her own profile.'); ?></p>
    </div>

    <div class="clearfix">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>

    <?php if ($multipleGroups) : ?>
        <div class="tab-menu permission-group-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($groups as $groupId => $groupTitle) : ?>
                    <li role="presentation" class="<?php if ($groupId == $group): ?>active<?php endif; ?>">
                        <a href="<?= Url::to(['permissions', 'groupId' => $groupId]); ?>" role="tab" ><?= Html::encode($groupTitle); ?></a>
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
