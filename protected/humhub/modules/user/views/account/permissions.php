<?php

use humhub\helpers\Html;
use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/** @var $multipleGroups bool * */
/** @var $group string * */
/** @var $groups string[] * */
/** @var $user \humhub\modules\user\models\User  * */
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Permissions</strong>'); ?>
</div>
<div class="panel-body">

    <div class="panel-body">
        <p class="text-body-secondary"><?= Yii::t('UserModule.account', 'These settings allow you to determine which permissions you want to grant visitors of your own individual profile. Each user can freely adjust the settings for his or her own profile.'); ?></p>
    </div>

    <div class="clearfix">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>

    <?php if ($multipleGroups) : ?>
        <div class="tab-menu permission-group-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($groups as $groupId => $groupTitle) : ?>
                    <li role="presentation" class="nav-item">
                        <a class="nav-link<?= ($groupId == $group) ? ' active' : '' ?>" href="<?= Url::to(['permissions', 'groupId' => $groupId]); ?>"
                           role="tab"><?= Html::encode($groupTitle); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <br/>
    <?php endif; ?>

    <div class="tab-content">
        <?= PermissionGridEditor::widget(['permissionManager' => $user->permissionManager, 'groupId' => $group]); ?>
    </div>
</div>
