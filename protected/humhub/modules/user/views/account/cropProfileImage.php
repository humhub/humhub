<?php

use humhub\compat\CActiveForm;
use yii\helpers\Url;
?>

<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = CActiveForm::begin(['id' => 'profile-crop-image-form']); ?>
        <?= $form->errorSummary($model); ?>
        <?= $form->hiddenField($model, 'cropX', ['id' => 'cropX']); ?>
        <?= $form->hiddenField($model, 'cropY', ['id' => 'cropY']); ?>
        <?= $form->hiddenField($model, 'cropW', ['id' => 'cropW']); ?>
        <?= $form->hiddenField($model, 'cropH', ['id' => 'cropH']); ?>

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?= Yii::t('UserModule.views_profile_cropProfileImage', '<strong>Modify</strong> your profile image'); ?></h4>
        </div>
        <div class="modal-body">

            <style>
                /* Dirty Workaround against bootstrap and jcrop */
                img {
                    max-width: none;
                }

                .jcrop-keymgr {
                    display: none !important;
                }

            </style>

            <div id="cropimage">
                <?= \yii\helpers\Html::img($profileImage->getUrl('_org'), ['id' => 'foobar']);

                echo raoul2000\jcrop\JCropWidget::widget([
                    'selector' => '#foobar',
                    'pluginOptions' => [
                        'aspectRatio' => 1,
                        'minSize' => [50, 50],
                        //'maxSize' => [200, 200],
                        'setSelect' => [0, 0, 100, 100],
                        'bgColor' => 'black',
                        'bgOpacity' => '0.5',
                        'boxWidth' => '440',
                        'onChange' => new yii\web\JsExpression('function(c){ $("#cropX").val(c.x);$("#cropY").val(c.y);$("#cropW").val(c.w);$("#cropH").val(c.h); }')
                    ]
                ]);
                ?>
            </div>
        </div>
        <div class="modal-footer">

            <?= \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.views_profile_cropProfileImage', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'id' => 'blabla',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => Url::toRoute(['/user/account/crop-profile-image', 'userGuid' => $user->guid]),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>

            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('UserModule.views_profile_cropProfileImage', 'Close'); ?>
            </button>

            <?= \humhub\widgets\LoaderWidget::widget(['id' => 'crop-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?= \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>
    </div>

</div>