<?php

use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ModuleCard;
use humhub\modules\user\models\User;
use humhub\modules\ui\view\helpers\ThemeHelper;

/* @var User $user */
/* @var ContentContainerModule[] $modules */

ModuleAsset::register($this);
?>
<div class="<?php if (ThemeHelper::isFluid()) : ?>container-fluid<?php else: ?>container<?php endif; ?> container-directory container-modules container-content-modules">
    <h4><?= Yii::t('UserModule.manage', '<strong>User</strong> modules'); ?></h4>

    <div class="row cards">
        <?php if (empty($modules)) : ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?= Yii::t('UserModule.manage', 'Currently there are no modules available for you!'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($modules as $module) : ?>
            <?= ModuleCard::widget([
                'contentContainer' => $user,
                'module' => $module,
            ]); ?>
        <?php endforeach; ?>
    </div>
</div>