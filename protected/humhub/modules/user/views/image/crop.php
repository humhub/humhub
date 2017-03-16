<?php

use humhub\compat\CActiveForm;
use yii\helpers\Url;
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = CActiveForm::begin([]); ?>
        <?php echo $form->hiddenField($model, 'cropX', ['id' => 'cropX']); ?>
        <?php echo $form->hiddenField($model, 'cropY', ['id' => 'cropY']); ?>
        <?php echo $form->hiddenField($model, 'cropW', ['id' => 'cropW']); ?>
        <?php echo $form->hiddenField($model, 'cropH', ['id' => 'cropH']); ?>

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?= $title; ?></h4>
        </div>
        <div class="modal-body">
            <style>
                /* Dirty Workaround against bootstrap and jcrop */
                img {
                    max-width: none;
                }
            </style>

            <div id="cropimage">
                <?php
                echo \yii\helpers\Html::img($profileImage->getUrl('_org'), ['id' => 'foobar']);

                echo raoul2000\jcrop\JCropWidget::widget([
                    'selector' => '#foobar',
                    'pluginOptions' => [
                        'aspectRatio' => $model->aspectRatio,
                        'minSize' => [50, 50],
                        'setSelect' => $model->cropSetSelect,
                        'bgColor' => 'black',
                        'boxWidth' => '440',
                        'bgOpacity' => '0.5',
                        'onChange' => new yii\web\JsExpression('function(c){ $("#cropX").val(c.x);$("#cropY").val(c.y);$("#cropW").val(c.w);$("#cropH").val(c.h); }')
                    ]
                ]);
                ?>
            </div>


        </div>
        <div class="modal-footer">
            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.views_profile_cropBannerImage', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => Url::to(['/user/image/crop', 'userGuid' => $user->guid, 'type' => $type]),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('UserModule.views_profile_cropBannerImage', 'Close'); ?></button>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'crop-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>
    </div>

</div>


