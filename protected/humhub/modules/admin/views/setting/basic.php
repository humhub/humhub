<?php

use humhub\compat\CHtml;
use yii\widgets\ActiveForm;
?>

<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.setting', 'General Settings'); ?></h4>
    <div class="help-block">
        <?php echo Yii::t('AdminModule.setting', 'Here you can configure basic settings of your social network.'); ?>
    </div>

    <br />

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'name'); ?>

    <?php echo $form->field($model, 'baseUrl'); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'E.g. http://example.com/humhub'); ?></p>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?php echo $languageDropDown = $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'timeZone')->dropDownList(\humhub\libs\TimezoneHelper::generateList()); ?>
    
    <?= humhub\modules\space\widgets\SpacePickerField::widget([
        'form' => $form,
        'model' => $model,
        'attribute' => 'defaultSpaceGuid',
        'selection' => $model->defaultSpaces
    ])?>
    
    <?php echo $form->field($model, 'defaultStreamSort')->dropDownList($model->getDefaultStreamSortOptions()); ?>

    <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'New users will automatically be added to these space(s).'); ?></p>


    <strong><?php echo Yii::t('AdminModule.views_setting_index', 'Dashboard'); ?></strong>
    <br>
    <br>
    <?php echo $form->field($model, 'tour')->checkbox(); ?>
    <?php echo $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>

    <strong><?php echo Yii::t('AdminModule.views_setting_index', 'Friendship'); ?></strong>
    <br>
    <br>
    <?php echo $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_index', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

    <!-- show flash message after saving -->
    <?php \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>