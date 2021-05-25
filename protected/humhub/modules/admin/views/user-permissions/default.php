<?php

use humhub\libs\Html;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\user\widgets\PermisionGridModuleFilter;
use humhub\modules\user\widgets\PermissionGridEditor;
use yii\helpers\Url;

/* @var $defaultPermissionManager \humhub\modules\content\components\ContentContainerDefaultPermissionManager */
/* @var $groups array */
/* @var $groupId string */


AdminAsset::register($this);

$this->registerJsConfig('admin', $adminSettingsJsConfig = ['text' => [
    'enableProfilePermissions.header' => Yii::t('AdminModule.user', '<strong>Profile</strong> Permissions'),
    'enableProfilePermissions.question.enable' => Yii::t('AdminModule.user', 'Allow users to set individual permissions for their own profile?'),
    'enableProfilePermissions.button.enable' => Yii::t('AdminModule.user', 'Allow'),

    'enableProfilePermissions.question.disable' => Yii::t('AdminModule.user',
            'Deactivate individual profile permissions?') . '<br><br>' .
        '<div class="alert alert-danger">' .
        Yii::t('AdminModule.user', '<strong>Warning:</strong> All individual profile permission settings are reset to the default values!') .
        '</div>',
    'enableProfilePermissions.button.disable' => Yii::t('AdminModule.user', 'Deactivate'),
]]);

/** @var \humhub\modules\user\Module $userModule */
$userModule = Yii::$app->getModule('user');
$enabledProfilePermissions = (boolean)$userModule->settings->get('enableProfilePermissions', false);

?>
<?php $this->beginContent('@admin/views/authentication/_authenticationLayout.php') ?>

<div class="panel-body">
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'This option allows you to determine whether users may set individual permissions for their own profiles.'); ?>
    </div>
    <br/>
    <div class="checkbox">
        <label for="switchPermissionChkId">
            <?= Html::checkbox('switchPermissionChkName', $enabledProfilePermissions, [
                'id' => 'switchPermissionChkId',
                'data-action-click' => 'admin.changeIndividualProfilePermissions',
                'data-action-url' => Url::to(['/admin/user-permissions/switch-individual-profile-permissions']),
                'data-action-confirm-header' => $adminSettingsJsConfig['text']['enableProfilePermissions.header'],
                'data-action-confirm' => $adminSettingsJsConfig['text']['enableProfilePermissions.question.' . ($enabledProfilePermissions ? 'disable' : 'enable')],
                'data-action-confirm-text' => $adminSettingsJsConfig['text']['enableProfilePermissions.button.' . ($enabledProfilePermissions ? 'disable' : 'enable')],
            ]); ?>
            <?= Yii::t('AdminModule.user', 'Enable individual profile permissions'); ?>
        </label>
    </div>

    <br/>
    <br/>

    <h5><?= Yii::t('AdminModule.user', 'Default Profile Permissions'); ?></h5>
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'If individual profile permissions are not allowed, the following settings are unchangeable for all users. If individual profile permissions are allowed, the settings are only set as defaults that users can customise. The following entries are then displayed in the same form in the users profile settings:'); ?>
    </div>
    <br/>

    <div class="clearfix">
        <?= PermisionGridModuleFilter::widget() ?>
    </div>

    <ul id="tabs" class="nav nav-tabs tab-sub-menu permission-group-tabs">
        <?php foreach ($groups as $currentGroupId => $groupLabel) : ?>
            <li class="<?= ($groupId === $currentGroupId) ? 'active' : '' ?>">
                <a href="<?= Url::toRoute(['/admin/user-permissions', 'groupId' => $currentGroupId]) ?>"><?= $groupLabel ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="panel-body" style="padding-top: 0px;">
        <?= PermissionGridEditor::widget(['permissionManager' => $defaultPermissionManager, 'groupId' => $groupId]); ?>
    </div>
</div>

<?php $this->endContent() ?>
