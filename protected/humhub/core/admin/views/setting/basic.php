<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_index', '<strong>Basic</strong> settings'); ?></div>
    <div class="panel-body">
        <?php $form = CActiveForm::begin(['id' => 'basic-settings-form']); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array('class' => 'form-control', 'readonly' => Setting::IsFixed('name'))); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'baseUrl'); ?>
            <?php echo $form->textField($model, 'baseUrl', array('class' => 'form-control', 'readonly' => Setting::IsFixed('baseUrl'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'E.g. http://example.com/humhub'); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'defaultLanguage'); ?>
            <?php echo $form->dropDownList($model, 'defaultLanguage', Yii::$app->params['availableLanguages'], array('class' => 'form-control', 'readonly' => Setting::IsFixed('defaultLanguage'))); ?>
        </div>


        <?php echo $form->labelEx($model, 'defaultSpaceGuid'); ?>
        <?php echo $form->textField($model, 'defaultSpaceGuid', array('class' => 'form-control', 'id' => 'space_select')); ?>

        <?php /*
          \humhub\core\space\widgets\SpacePickerWidget::widget([
          'inputId' => 'space_select',
          'model' => $model,
          'maxSpaces' => 50,
          'attribute' => 'defaultSpaceGuid'
          ]);
         */
        ?>


        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'New users will automatically added to these space(s).'); ?></p>


        <strong><?php echo Yii::t('AdminModule.views_setting_index', 'Dashboard'); ?></strong>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'tour'); ?> <?php echo $model->getAttributeLabel('tour'); ?>
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'dashboardShowProfilePostForm'); ?> <?php echo $model->getAttributeLabel('dashboardShowProfilePostForm'); ?>
                </label>
            </div>
        </div>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_index', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php \humhub\widgets\DataSaved::widget(); ?>

        <?php CActiveForm::end(); ?>

    </div>
</div>

