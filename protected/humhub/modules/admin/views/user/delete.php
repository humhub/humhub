<?php

use humhub\libs\Html;
use humhub\modules\admin\models\forms\UserDeleteForm;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;

/* @var $model UserDeleteForm */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Confirm user deletion'); ?></h4>
    <br>

    <p><strong><?= Yii::t('AdminModule.user', 'Are you sure that you want to delete following user?'); ?></strong></p>
    <div class="well">

    <div class="media">
        <div class="media-left" style="padding-right:6px">
            <?= UserImage::widget(['user' => $model->user, 'width' => 38, 'link' => true]); ?>
        </div>
        <div class="media-body">
            <h4 class="media-heading"><?= Html::containerLink($model->user); ?></h4>
            <?= Html::encode($model->user->email) ?>
        </div>
    </div>    
    <hr>
        <p><i class="fa fa-exclamation-triangle" style="color: <?= $this->theme->variable('danger')?>"></i> &nbsp;<?= Yii::t('AdminModule.user', 'All the personal data of this user will be irrevocably deleted.'); ?></p>

    <?php if (count($model->getOwningSpaces()) !== 0): ?>

        <p><b><?= Yii::t('AdminModule.user', 'The user is the owner of these spaces:'); ?></b></p>

        <?php foreach ($model->getOwningSpaces() as $space): ?>
            <div class="media">
                <div class="media-left" style="padding-right:6px">
                    <?= SpaceImage::widget(['space' => $space, 'width' => 38, 'link' => true]); ?>
                </div>
                <div class="media-body">
                    <h4 class="media-heading"><?= Html::containerLink($space); ?></h4>
                    <?= Yii::t('SpaceModule.base', '{count} members', ['count' => $space->getMemberships()->count()]); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?= Yii::t('AdminModule.user', 'This user owns no spaces.'); ?></p>
    <?php endif; ?>
    </div>

    <br />
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'deleteContributions')->checkbox(['disabled' => !$model->isAttributeSafe('deleteContributions')]); ?>
    <?php if (count($model->getOwningSpaces()) !== 0): ?>
        <?= $form->field($model, 'deleteSpaces')->checkbox(); ?>
    <?php endif; ?>

    <br />
    <hr>
    <?= Button::danger(Yii::t('UserModule.account', 'Delete account'))
        ->confirm()
        ->submit() ?>
    <?= Button::primary(Yii::t('AdminModule.user', 'Cancel'))
        ->link(['/admin/user/edit', 'id' => $model->user->id])
        ->right() ?>
    <?php ActiveForm::end(); ?>
</div>