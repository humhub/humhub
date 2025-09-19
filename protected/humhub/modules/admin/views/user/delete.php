<?php

use humhub\helpers\Html;
use humhub\modules\admin\models\forms\UserDeleteForm;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var $model UserDeleteForm */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Confirm user deletion') ?></h4>
    <br>

    <p><strong><?= Yii::t('AdminModule.user', 'Are you sure that you want to delete following user?') ?></strong></p>
    <div class="bg-light p-3">

        <div class="d-flex">
            <div class="flex-shrink-0 me-2">
                <?= UserImage::widget(['user' => $model->user, 'width' => 38, 'link' => true]) ?>
            </div>
            <div class="flex-grow-1">
                <h4 class="mt-0"><?= Html::containerLink($model->user) ?></h4>
                <?= Html::encode($model->user->email) ?>
            </div>
        </div>
        <hr>
        <p><i class="fa fa-exclamation-triangle" style="color:var(--danger)"></i>
            &nbsp;<?= Yii::t('AdminModule.user', 'All the personal data of this user will be irrevocably deleted.') ?>
        </p>

        <?php if (count($model->getOwningSpaces()) !== 0): ?>

            <p><b><?= Yii::t('AdminModule.user', 'The user is the owner of these spaces:') ?></b></p>

            <?php foreach ($model->getOwningSpaces() as $space): ?>
                <div class="d-flex">
                    <div class="flex-shrink-0 me-2">
                        <?= SpaceImage::widget(['space' => $space, 'width' => 38, 'link' => true]) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mt-0"><?= Html::containerLink($space) ?></h4>
                        <?= Yii::t('SpaceModule.base', '{count} members', ['count' => $space->getMemberships()->count()]) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?= Yii::t('AdminModule.user', 'This user owns no spaces.'); ?></p>
        <?php endif; ?>
    </div>

    <br/>
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'deleteContributions')->checkbox(['disabled' => !$model->isAttributeSafe('deleteContributions')]) ?>
    <?php if (count($model->getOwningSpaces()) !== 0): ?>
        <?= $form->field($model, 'deleteSpaces')->checkbox() ?>
    <?php endif; ?>

    <br/>
    <hr>
    <?= Button::danger(Yii::t('UserModule.account', 'Delete account'))
        ->confirm()
        ->submit() ?>
    <?= Button::primary(Yii::t('AdminModule.user', 'Cancel'))
        ->link(['/admin/user/edit', 'id' => $model->user->id])
        ->right() ?>
    <?php ActiveForm::end(); ?>
</div>
