<?php

use humhub\modules\content\widgets\ModuleCard;
use humhub\modules\space\assets\SpaceAsset;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\components\View;
use yii\helpers\Url;

/* @var $availableModules array available modules for space */
/* @var $space Space */
/* @var $this View */

SpaceAsset::register($this);
?>
<div class="modal-dialog modal-dialog-medium animated fadeIn" style="width:100%;max-width:900px">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('SpaceModule.manage', 'Add <strong>Modules</strong>') ?>
            </h4>
            <div class="help-block"><?= Yii::t('SpaceModule.manage', 'Choose the modules you want to use for this Space. If you are undecided, you can also activate them later on via the Space settings.') ?></div>
        </div>
        <div class="modal-body">
            <div class="container container-cards container-modules container-create-space-modules">
                <div class="row cards">
                    <?php foreach ($availableModules as $moduleId => $module) : ?>
                        <?= ModuleCard::widget([
                            'contentContainer' => $space,
                            'module' => $module,
                            'view' => '@humhub/modules/space/views/create/moduleCard'
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <a href="#" class="btn btn-info"
               data-action-click="ui.modal.post"
               data-ui-loader
               data-action-url="<?= Url::to(['/space/create/invite', 'spaceId' => $space->id]); ?>">
                <?= Yii::t('SpaceModule.manage', 'Next'); ?>
            </a>
        </div>
    </div>
</div>
