<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_design', '<strong>Design</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'design-settings-form',
            'enableAjaxValidation' => false,
            'htmlOptions' => array('enctype' => 'multipart/form-data'),
        ));
        ?>

        <?php echo $form->errorSummary($model); ?><br>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'theme'); ?>
            <?php echo $form->dropDownList($model, 'theme', $themes, array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'paginationSize'); ?>
            <?php echo $form->textField($model, 'paginationSize', array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'displayName'); ?>
            <?php echo $form->dropDownList($model, 'displayName', array('{username}' => Yii::t('AdminModule.views_setting_design', 'Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.views_setting_design', 'Firstname Lastname (e.g. John Doe)')), array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'spaceOrder'); ?>
            <?php echo $form->dropDownList($model, 'spaceOrder', array('0' => Yii::t('AdminModule.views_setting_design', 'Alphabetical'), '1' => Yii::t('AdminModule.views_setting_design', 'Last visit')), array('class' => 'form-control')); ?>
        </div>


        <?php echo $form->labelEx($model, 'logo'); ?>
        <?php echo $form->fileField($model, 'logo', array('id' => 'logo', 'style' => 'display: none', 'name' => 'logo[]')); ?>
        <?php echo $form->error($model, 'logo'); ?>

        <div class="well">
            <div class="image-upload-container" id="logo-upload">

                <img class="img-rounded" id="logo-image"
                     src="<?php
                     if ($logo->hasImage()) {
                         echo $logo->getUrl();
                     }
                     ?>"
                     data-src="holder.js/140x140"
                     alt="<?php echo Yii::t('AdminModule.views_setting_index', "You're using no logo at the moment. Upload your logo now."); ?>"
                     style="max-height: 40px;"/>

                <div class="image-upload-buttons" id="logo-upload-buttons" style="display: block;">
                    <a href="#" onclick="javascript:$('#logo').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>

                    <?php
                    $this->widget('application.widgets.ModalConfirmWidget', array(
                        'uniqueID' => 'modal_logoimagedelete',
                        'linkOutput' => 'a',
                        'title' => Yii::t('AdminModule.views_setting_index', '<strong>Confirm</strong> image deleting'),
                        'message' => Yii::t('UserModule.views_setting_index', 'Do you really want to delete your logo image?'),
                        'buttonTrue' => Yii::t('AdminModule.views_setting_index', 'Delete'),
                        'buttonFalse' => Yii::t('AdminModule.views_setting_index', 'Cancel'),
                        'linkContent' => '<i class="fa fa-times"></i>',
                        'class' => 'btn btn-danger btn-sm',
                        'style' => $logo->hasImage() ? '' : 'display: none;',
                        'linkHref' => $this->createUrl("//admin/setting/deleteLogoImage"),
                        'confirmJS' => 'function(jsonResp) { resetLogoImage(jsonResp); }'
                    ));
                    ?>
                </div>
            </div>
        </div>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_design', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

<?php $this->endWidget(); ?>

    </div>
</div>




