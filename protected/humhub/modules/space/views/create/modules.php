<?php

use humhub\components\View;
use humhub\modules\space\assets\SpaceAsset;
use humhub\modules\space\models\Space;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/* @var $availableModules array available modules for space */
/* @var $space Space */
/* @var $this View */

SpaceAsset::register($this);
?>

<?php Modal::beginDialog([
    'closeButton' => false,
    'title' => Yii::t('SpaceModule.manage', 'Add <strong>Modules</strong>'),
    'footer' => ModalButton::info(Yii::t('SpaceModule.manage', 'Next'))
        ->action('ui.modal.post', Url::to(['/space/create/invite', 'spaceId' => $space->id])),
]) ?>
    <div class="text-body-secondary">
        <?= Yii::t('SpaceModule.manage', 'Choose the modules you want to use for this Space. If you are undecided, you can also activate them later on via the Space settings.') ?>
    </div>

    <div class="container container-cards container-modules container-create-space-modules">
        <div class="modules-group">
            <?php foreach ($availableModules as $module) : ?>
                <?= $this->render('module-entry', [
                    'space' => $space,
                    'module' => $module,
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php Modal::endDialog() ?>
