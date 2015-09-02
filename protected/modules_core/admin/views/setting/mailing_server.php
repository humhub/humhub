<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_mailing_server', '<strong>Mailing</strong> settings'); ?></div>
    <div class="panel-body">

        <ul class="nav nav-pills">
            <li><a
                    href="<?php echo $this->createUrl('mailing'); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing_server', 'Defaults'); ?></a>
            </li>
            <li class="active">
                <a href="<?php echo $this->createUrl('mailingServer'); ?>"><?php echo Yii::t('AdminModule.views_setting_mailing_server', 'Server Settings'); ?></a>
            </li>
        </ul>
        <br />
        
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'mailing-settings-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'systemEmailAddress'); ?>
            <?php echo $form->textField($model, 'systemEmailAddress', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('systemEmailAddress', 'mailing'))); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'systemEmailName'); ?>
            <?php echo $form->textField($model, 'systemEmailName', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('systemEmailName', 'mailing'))); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'transportType'); ?>
            <?php echo $form->dropDownList($model, 'transportType', $transportTypes, array('class' => 'form-control', 'readonly' => HSetting::IsFixed('transportType', 'mailing'))); ?>
        </div>

        <div id="smtpOptions">
            <hr>
            <h4> <?php echo Yii::t('AdminModule.views_setting_mailing_server', 'SMTP Options'); ?> </h4>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'hostname'); ?>
                <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('hostname', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'username'); ?>
                <?php echo $form->textField($model, 'username', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('username', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'password'); ?>
                <?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('password', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'port'); ?>
                <?php echo $form->textField($model, 'port', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('port', 'mailing'))); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'encryption'); ?>
                <?php echo $form->dropDownList($model, 'encryption', $encryptionTypes, array('class' => 'form-control', 'readonly' => HSetting::IsFixed('encryption', 'mailing'))); ?>
            </div>
            
            <div id="encryptionOptions">
            	<div class="form-group">
            		<strong>Encryption Options</strong>
            		<div class="checkbox">
                		<label>
                			<?php echo $form->checkbox($model, 'allowSelfSignedCerts', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('allowSelfSignedCerts', 'mailing'))); ?>
							<?php echo $model->getAttributeLabel('allowSelfSignedCerts'); ?>
            			</label>
            		</div>
            	</div>
            </div>
        </div>
        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_mailing_server', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>

<script>

    if ($("#MailingSettingsForm_transportType option:selected").val() != 'smtp') {
        $("#smtpOptions").hide();
    }

    $('#MailingSettingsForm_transportType').on('change', function() {
        if ($("#MailingSettingsForm_transportType option:selected").val() != 'smtp') {
            $("#smtpOptions").hide();
        } else {
            $("#smtpOptions").show();
        }
    });

    if ($("#MailingSettingsForm_encryption option:selected").val() == '') {
        $("#encryptionOptions").hide();
    }

    $('#MailingSettingsForm_encryption').on('change', function() {
        if ($("#MailingSettingsForm_encryption option:selected").val() == '') {
            $("#encryptionOptions").hide();
        } else {
            $("#encryptionOptions").show();
        }
    });
</script>    



