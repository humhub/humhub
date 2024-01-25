<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModule;
use humhub\modules\user\models\User;

/* @var User $user */
/* @var ContentContainerModule[] $modules */
?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('UserModule.manage', '<strong>Profile</strong> modules') ?>
        <div class="form-text"><?= Yii::t('UserModule.manage', 'Similar to Spaces, your personal profile also allows you to use modules. Please keep in mind that information you share on your profile is available to other users of the network.') ?></div>
    </div>
    <div class="card-body">
        <?php if (empty($modules)) : ?>
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-body">
                        <?= Yii::t('UserModule.manage', 'Currently there are no modules available for you!'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="modules-group">
            <?php foreach ($modules as $module) : ?>
                <?= ContainerModule::widget([
                    'contentContainer' => $user,
                    'module' => $module,
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
