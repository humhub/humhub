<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModule;
use humhub\modules\user\models\User;

/* @var User $user */
/* @var ContentContainerModule[] $modules */
?>
<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
<p>
    <?= Yii::t('UserModule.manage', 'Similar to Spaces, your personal profile also allows you to use modules. Please keep in mind that information you share on your profile is available to other users of the network.') ?>
</p>
<?php if (empty($modules)) : ?>
    <p><?= Yii::t('UserModule.manage', 'Currently there are no modules available for you!') ?></p>
<?php endif; ?>

<div class="modules-group">
    <?php foreach ($modules as $module) : ?>
        <?= ContainerModule::widget([
            'contentContainer' => $user,
            'module' => $module,
        ]); ?>
    <?php endforeach; ?>
</div>
<?php $this->endContent() ?>
