<?php

use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\user\models\User;
use humhub\widgets\AjaxButton;
use humhub\widgets\LoaderWidget;

/**
 * @var $module \humhub\components\Module
 * @var $model \humhub\modules\admin\models\forms\ModuleSetAsDefaultForm
 */

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
            </div>
        </div>

        <div class="modal-footer">
            <?= AjaxButton::widget([
                'label' => Yii::t('AdminModule.modules', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => \yii\helpers\Url::to(['/admin/module/set-as-default', 'moduleId' => $module->id]),
                ],
                'htmlOptions' => ['class' => 'btn btn-primary']
            ]); ?>

            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('AdminModule.modules', 'Close'); ?>
            </button>

            <?= LoaderWidget::widget(['id' => 'default-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?php $form::end(); ?>

    </div>
</div>
