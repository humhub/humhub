<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\DataSaved;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="panel panel-default">

    <div>
        <div class="panel-heading">
            <?= Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>

    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false]); ?>

        <?php if (Yii::$app->urlManager->enablePrettyUrl) : ?>
            <?= $form->field($model, 'url')->hint(Yii::t('SpaceModule.manage', 'e.g. example for {baseUrl}/s/example', ['baseUrl' => Url::base(true)])); ?>
        <?php endif; ?>
        <?= $form->field($model, 'indexUrl')->dropDownList($indexModuleSelection)->hint(Yii::t('SpaceModule.manage', 'the default start page of this space for members')) ?>
        <?= $form->field($model, 'indexGuestUrl')->dropDownList($indexModuleSelection)->hint(Yii::t('SpaceModule.manage', 'the default start page of this space for visitors')) ?>

        <?= Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?= DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->status == Space::STATUS_ENABLED || $model->status == Space::STATUS_ARCHIVED) : ?> 
                <a href="#" <?= $model->status == Space::STATUS_ARCHIVED ? 'style="display:none;"' : '' ?> class="btn btn-warning archive"
                   data-action-click="space.archive" data-action-url="<?= $model->createUrl('/space/manage/default/archive') ?>" data-ui-loader>
                    <?= Yii::t('SpaceModule.views_admin_edit', 'Archive'); ?>
                </a>
                <a href="#" <?= $model->status == Space::STATUS_ENABLED ? 'style="display:none;"' : '' ?> class="btn btn-warning unarchive"
                   data-action-click="space.unarchive" data-action-url="<?= $model->createUrl('/space/manage/default/unarchive') ?>" data-ui-loader>
                    <?= Yii::t('SpaceModule.views_admin_edit', 'Unarchive'); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
