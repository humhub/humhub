<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = $this->beginWidget('CActiveForm', array('id' => 'workspace-crop-image-form', 'enableAjaxValidation' => false)); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('UserModule.base', '<strong>Modify</strong> your profile image'); ?></h4>
        </div>
        <div class="modal-body">

            <p><?php //echo Yii::t('UserModule.base', 'Select the area of your image you want to save as user avatar and click <strong>Save</strong>.'); ?></p>


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

            <?php echo HHtml::ajaxButton(Yii::t('UserModule.account', 'Save'), array('//user/profile/cropProfileImage'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ $("#invite-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));

            ?>

            <?php //echo CHtml::submitButton(Yii::t('UserModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>

            <div class="col-md-1 modal-loader">
                <div id="invite-loader" class="loader loader-small hidden"></div>
            </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>

<script type="text/javascript">
    /*
     * Modal handling by close event
     */
    $('#globalModal').on('hidden.bs.modal', function (e) {

        // Reload whole page (to see changes on it)
        //window.location.reload();

        // just close modal and reset modal content to default (shows the loader)
        $('#globalModal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
    })
</script>




