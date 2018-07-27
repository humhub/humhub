<?php

use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\space\widgets\Image as SpaceImage;
use yii\bootstrap\ActiveForm;

/* @var $model \yii\base\Model */
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
        <p><i class="fa fa-exclamation-triangle" style="color: <?= $this->theme->variable('danger')?>"></i> &nbsp;<?= Yii::t('AdminModule.account', 'All the personal data of this user will be irrevocably deleted.'); ?></p>

    <?php if (count($model->getOwningSpaces()) !== 0): ?>

        <p><b><?= Yii::t('AdminModule.account', 'The user is the owner of these spaces:'); ?></b></p>

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
        <p><?= Yii::t('AdminModule.account', 'This user owns no spaces.'); ?></p>
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
    <?= Html::submitButton(Yii::t('UserModule.account', 'Delete account'), ['class' => 'btn btn-danger', 'data-ui-loader' => '']); ?>
    <?= Html::a(Yii::t('AdminModule.user', 'Cancel'), Url::to(['/admin/user/edit', 'id' => $model->user->id]), ['class' => 'btn btn-primary pull-right']); ?>
    <?php ActiveForm::end(); ?>
</div>