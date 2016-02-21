<?php

use \yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use \humhub\models\Setting;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', '<strong>Security</strong> settings'); ?>
</div>
<div class="panel-body">

    <br />

    <ul class="nav nav-tabs" role="tablist">
        <?php foreach ($groups as $groupId => $groupTitle) : ?>
            <li role="presentation" class="<?php if ($groupId == $group): ?>active<?php endif; ?>">
                <a href="<?= Url::to(['security', 'groupId' => $groupId]); ?>" role="tab" ><?php echo $groupTitle; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?= \humhub\modules\user\widgets\PermissionGridEditor::widget(['permissionManager' => $user->permissionManager, 'groupId' => $group]); ?>
    </div>

</div>
