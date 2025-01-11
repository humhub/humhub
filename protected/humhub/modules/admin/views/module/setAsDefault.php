<?php

use humhub\components\Module;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\ModuleSetAsDefaultForm;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this View
 * @var $module Module
 * @var $model ModuleSetAsDefaultForm
 */

AdminAsset::register($this);

?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('AdminModule.modules', '%moduleName% - Set as default module', ['%moduleName%' => "<strong>" . $module->getName() . "</strong>"]),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')) . ' ' . ModalButton::primary(Yii::t('AdminModule.modules', 'Save'))->action('admin.moduleSetAsDefault', Url::to(['/admin/module/set-as-default', 'moduleId' => $module->id])),
]) ?>

    <p><?= Yii::t('AdminModule.modules', 'Here you can choose whether or not a module should be automatically activated on a space or user profile. If the module should be activated, choose "always activated".'); ?></p>
    <br>

    <div class="row">
        <?php if ($module->hasContentContainerType(Space::class)) : ?>
            <div class="col-lg-6">
                <?= $form->field($model, 'spaceDefaultState')->radioList($model->getStatesList())
                    ->label(Yii::t('AdminModule.modules', 'Spaces')); ?>
            </div>
        <?php endif; ?>

        <?php if ($module->hasContentContainerType(User::class)) : ?>
            <div class="col-lg-6">
                <?= $form->field($model, 'userDefaultState')->radioList($model->getStatesList())
                    ->label(Yii::t('AdminModule.modules', 'Users')); ?>
            </div>
        <?php endif; ?>
        <br>
        <?php if ($model->mustConfirmModuleDeactivation()) : ?>
            <div class="col-lg-12">
                <?= $form->field($model, 'moduleDeactivationConfirmed')->checkbox() ?>
            </div>
        <?php endif; ?>
    </div>

<?php Modal::endFormDialog(); ?>
