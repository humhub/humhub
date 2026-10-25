<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModule;
use humhub\modules\user\models\User;
use humhub\widgets\PageHeader;

/* @var User $user */
/* @var ContentContainerModule[] $modules */
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Modules')) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <p class="text-body-secondary">
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
        </div>
    </div>
</div>
