<?php

use humhub\compat\CActiveForm;
use humhub\widgets\AjaxButton;
use humhub\widgets\LoaderWidget;
use humhub\widgets\DataSaved;
use yii\helpers\Html;
use yii\web\JsExpression;
use raoul2000\jcrop\JCropWidget;
?>

<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = CActiveForm::begin(['id' => 'space-crop-image-form']); ?>
        <?= $form->errorSummary($model); ?>
        <?= $form->hiddenField($model, 'cropX', ['id' => 'cropX']); ?>
        <?= $form->hiddenField($model, 'cropY', ['id' => 'cropY']); ?>
        <?= $form->hiddenField($model, 'cropW', ['id' => 'cropW']); ?>
        <?= $form->hiddenField($model, 'cropH', ['id' => 'cropH']); ?>

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('SpaceModule.views_admin_cropImage', '<strong>Modify</strong> space image'); ?>
            </h4>
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
                <?= Html::img($profileImage->getUrl('_org'), ['id' => 'foobar']);

                echo JCropWidget::widget([
                    'selector' => '#foobar',
                    'pluginOptions' => [
                        'aspectRatio' => 1,
                        'minSize' => [50, 50],
                        'setSelect' => [0, 0, 100, 100],
                        'bgColor' => 'black',
                        'bgOpacity' => '0.5',
                        'boxWidth' => '440',
                        'onChange' => new JsExpression('function(c){ $("#cropX").val(c.x);$("#cropY").val(c.y);$("#cropW").val(c.w);$("#cropH").val(c.h); }')
                    ]
                ]);
                ?>
            </div>


        </div>
        <div class="modal-footer">

            <?= AjaxButton::widget([
                'label' => Yii::t('UserModule.views_profile_cropProfileImage', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new JsExpression('function(){ setModalLoader(); }'),
                    'success' => new JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => $space->createUrl('/space/manage/image/crop'),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>

            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('SpaceModule.views_admin_cropImage', 'Close'); ?>
            </button>

            <?= LoaderWidget::widget(['id' => 'crop-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?= DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>
    </div>

</div>
