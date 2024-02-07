<?php

use humhub\components\Module;
use humhub\libs\Html;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\ModuleSetAsDefaultForm;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\models\User;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 * @var $module Module
 * @var $model ModuleSetAsDefaultForm
 */

AdminAsset::register($this);

?>

<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('AdminModule.modules', '%moduleName% - Set as default module', ['%moduleName%' => "<strong>" . $module->getName() . "</strong>"]); ?>
            </h4>
        </div>

        <?php $form = ActiveForm::begin(); ?>
        <div class="modal-body">
            <p><?= Yii::t('AdminModule.modules', 'Here you can choose whether or not a module should be automatically activated on a space or user profile. If the module should be activated, choose "always activated".'); ?></p>
            <br>

            <div class="row">
                <?php if ($module->hasContentContainerType(Space::class)) : ?>
                    <div class="col-md-6">
                        <?= $form->field($model, 'spaceDefaultState')->radioList($model->getStatesList())
                            ->label(Yii::t('AdminModule.modules', 'Spaces')); ?>
                    </div>
                <?php endif; ?>

                <?php if ($module->hasContentContainerType(User::class)) : ?>
                    <div class="col-md-6">
                        <?= $form->field($model, 'userDefaultState')->radioList($model->getStatesList())
                            ->label(Yii::t('AdminModule.modules', 'Users')); ?>
                    </div>
                <?php endif; ?>
                <br>
                <?php if ($model->mustConfirmModuleDeactivation()) : ?>
                    <div class="col-md-12">
                        <?= $form->field($model, 'moduleDeactivationConfirmed')->checkbox() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal-footer">
            <?= Html::a(
                Yii::t('AdminModule.modules', 'Save'),
                '#',
                [
                    'class' => ['btn', 'btn-primary'],
                    'data' => [
                        'action-click' => 'admin.moduleSetAsDefault',
                        'action-url' => Url::to(['/admin/module/set-as-default', 'moduleId' => $module->id]),
                    ]
                ]
            ) ?>
            <?= Html::button(
                Yii::t('AdminModule.modules', 'Close'),
                [
                    'class' => ['btn', 'btn-primary'],
                    'data' => [
                        'dismiss' => 'modal',
                    ]
                ]
            ) ?>
        </div>

        <?php $form::end(); ?>

    </div>
</div>
