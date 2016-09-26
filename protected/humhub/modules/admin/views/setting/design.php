<?php

use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Url;

$this->registerJsFile('@web/resources/admin/uploadLogo.js');
?>

<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.setting', 'Appearance Settings'); ?></h4>
    <div class="help-block">
        <?php echo Yii::t('AdminModule.setting', 'These settings refer to the appearance of your social network.'); ?>
    </div>
    
    <br />
    
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>


    <?php echo $form->field($model, 'theme')->dropdownList($themes); ?>

    <?php echo $form->field($model, 'paginationSize'); ?>

    <?php echo $form->field($model, 'displayName')->dropdownList(['{username}' => Yii::t('AdminModule.views_setting_design', 'Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.views_setting_design', 'Firstname Lastname (e.g. John Doe)')]); ?>

    <?php echo $form->field($model, 'spaceOrder')->dropdownList(['0' => Yii::t('AdminModule.views_setting_design', 'Alphabetical'), '1' => Yii::t('AdminModule.views_setting_design', 'Last visit')]); ?>

    <?php
    echo $form->field($model, 'dateInputDisplayFormat')->dropdownList([
        '' => Yii::t('AdminModule.views_setting_design', 'Auto format based on user language - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'short')]),
        'php:d/m/Y' => Yii::t('AdminModule.views_setting_design', 'Fixed format (mm/dd/yyyy) - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'php:d/m/Y')]),
    ]);
    ?>
    <strong><?php echo Yii::t('AdminModule.views_setting_index', 'Wall entry layout'); ?></strong>
    <br>
    <br>
    <?php echo $form->field($model, 'horImageScrollOnMobile')->checkbox(); ?>

    <?php echo $form->field($model, 'logo')->fileInput(['id' => 'logo', 'style' => 'display: none', 'name' => 'logo[]']); ?>

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
                echo \humhub\widgets\ModalConfirm::widget(array(
                    'uniqueID' => 'modal_logoimagedelete',
                    'linkOutput' => 'a',
                    'title' => Yii::t('AdminModule.views_setting_index', '<strong>Confirm</strong> image deleting'),
                    'message' => Yii::t('UserModule.views_setting_index', 'Do you really want to delete your logo image?'),
                    'buttonTrue' => Yii::t('AdminModule.views_setting_index', 'Delete'),
                    'buttonFalse' => Yii::t('AdminModule.views_setting_index', 'Cancel'),
                    'linkContent' => '<i class="fa fa-times"></i>',
                    'cssClass' => 'btn btn-danger btn-sm',
                    'style' => $logo->hasImage() ? '' : 'display: none;',
                    'linkHref' => Url::toRoute("/admin/setting/delete-logo-image"),
                    'confirmJS' => 'function(jsonResp) { resetLogoImage(jsonResp); }'
                ));
                ?>
            </div>
        </div>
    </div>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_design', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

    <?php echo \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>