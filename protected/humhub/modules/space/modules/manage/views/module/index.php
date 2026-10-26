<?php

use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\widgets\ContainerModule;
use humhub\modules\space\models\Space;
use humhub\widgets\ContentCard;
use yii\web\View;

/* @var View $this */
/* @var Space $space */
/* @var ContentContainerModule[] $availableModules */
?>
<?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
    'title' => Yii::t('SpaceModule.manage', 'Modules'),
]) ?>
<?php ContentCard::begin() ?>
    <p><?= Yii::t('SpaceModule.manage', 'Choose the modules you want to use for this Space. In order for the modules to be available to you here, they must have been previously installed by administrators of the network using the admin panel. If you cannot deactivate individual modules, it is because they have been set as the default for the entire network.') ?></p>

    <?php if (empty($availableModules)) : ?>
        <p><?= Yii::t('SpaceModule.manage', 'Currently there are no modules available for this space!'); ?></p>
    <?php endif; ?>

    <div class="modules-group">
        <?php foreach ($availableModules as $module) : ?>
            <?= ContainerModule::widget([
                'contentContainer' => $space,
                'module' => $module,
            ]); ?>
        <?php endforeach; ?>
    </div>
<?php ContentCard::end() ?>
<?php $this->endContent() ?>
