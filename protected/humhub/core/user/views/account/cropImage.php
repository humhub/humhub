<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = $this->beginWidget('CActiveForm', array('id' => 'workspace-crop-image-form', 'enableAjaxValidation' => false)); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('UserModule.views_account_cropImage', '<strong>Modify</strong> your profile image'); ?></h4>
        </div>
        <div class="modal-body">

            <p><?php //echo Yii::t('UserModule.views_account_cropImage', 'Select the area of your image you want to save as user avatar and click <strong>Save</strong>.'); ?></p>


            <?php echo $form->errorSummary($model); ?>
            <?php echo $form->hiddenField($model, 'cropX'); ?>
            <?php echo $form->hiddenField($model, 'cropY'); ?>
            <?php echo $form->hiddenField($model, 'cropW'); ?>
            <?php echo $form->hiddenField($model, 'cropH'); ?>

            <style>
                /* Dirty Workaround against bootstrap and jcrop */
                img {
                    max-width: none
                }

                .jcrop-keymgr {
                    display: none !important;
                }

            </style>

            <div id="cropimage">
                <?php $this->widget('ext.yii-jcrop.jCropWidget', array(
                        'imageUrl' => $profileImage->getUrl('_org') . "?nocache=" . time(),
                        'formElementX' => 'CropProfileImageForm_cropX',
                        'formElementY' => 'CropProfileImageForm_cropY',
                        'formElementWidth' => 'CropProfileImageForm_cropW',
                        'formElementHeight' => 'CropProfileImageForm_cropH',
                        'jCropOptions' => array(
                            'aspectRatio' => 1,
                            'boxWidth' => 400,
                            'setSelect' => array(0, 0, 100, 100),
                        ),
                    )
                );
                ?>
            </div>


        </div>
        <div class="modal-footer">

            <?php echo HHtml::ajaxButton(Yii::t('UserModule.views_account_cropImage', 'Save'), array('//user/account/cropImage'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ setModalLoader(); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));

            ?>

            <?php //echo CHtml::submitButton(Yii::t('UserModule.views_account_cropImage', 'Save'), array('class' => 'btn btn-primary')); ?>

            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('UserModule.views_account_cropImage', 'Close'); ?></button>

                <div id="crop-loader" class="loader loader-modal hidden">
                    <div class="sk-spinner sk-spinner-three-bounce">
                        <div class="sk-bounce1"></div>
                        <div class="sk-bounce2"></div>
                        <div class="sk-bounce3"></div>
                    </div>
                </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>





