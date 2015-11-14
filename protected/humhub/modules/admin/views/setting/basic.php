<?php

use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\widgets\ActiveForm;

?>

<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_index', '<strong>Basic</strong> settings'); ?></div>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(); ?>


        <?php echo $form->field($model, 'name'); ?>

        <?php echo $form->field($model, 'baseUrl'); ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'E.g. http://example.com/humhub'); ?></p>

        <?php echo $form->field($model, 'defaultLanguage')->dropdownList(Yii::$app->params['availableLanguages']); ?>

        <?php echo $form->field($model, 'timeZone')->dropdownList(\humhub\libs\TimezoneHelper::generateList()); ?>

        <?php echo $form->field($model, 'defaultSpaceGuid')->textInput(['id' => 'space_select']); ?>

        <?php
        echo \humhub\modules\space\widgets\Picker::widget([
            'inputId' => 'space_select',
            'model' => $model,
            'maxSpaces' => 50,
            'attribute' => 'defaultSpaceGuid'
        ]);
        ?>

        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'New users will automatically added to these space(s).'); ?></p>


        <strong><?php echo Yii::t('AdminModule.views_setting_index', 'Dashboard'); ?></strong>
        <br>
        <br>
        <?php echo $form->field($model, 'tour')->checkbox(); ?>
        <?php echo $form->field($model, 'share')->checkbox(); ?>
        <?php echo $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_index', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php \humhub\widgets\DataSaved::widget(); ?>

        <?php ActiveForm::end(); ?>

    </div>
</div>

