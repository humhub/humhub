<?php

use humhub\compat\CHtml;
use yii\widgets\ActiveForm;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'General Settings'); ?></h4>
    
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 'Here you can configure basic settings of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name'); ?>

    <?= $form->field($model, 'baseUrl'); ?>
    <p class="help-block"><?= Yii::t('AdminModule.views_setting_index', 'E.g. http://example.com/humhub'); ?></p>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?= $languageDropDown = $form->field($model, 'defaultLanguage')->dropdownList($allowedLanguages); ?>
    <?php endif; ?>

    <?= $form->field($model, 'timeZone')->dropdownList(\humhub\libs\TimezoneHelper::generateList()); ?>
    <?= $form->field($model, 'defaultSpaceGuid')->textInput(['id' => 'space_select']); ?>
    <?= $form->field($model, 'defaultStreamSort')->dropdownList($model->getDefaultStreamSortOptions()); ?>

    <?= \humhub\modules\space\widgets\Picker::widget([
        'inputId' => 'space_select',
        'model' => $model,
        'maxSpaces' => 50,
        'attribute' => 'defaultSpaceGuid'
    ]);
    ?>

    <p class="help-block"><?= Yii::t('AdminModule.views_setting_index', 'New users will automatically be added to these space(s).'); ?></p>

    <strong><?= Yii::t('AdminModule.views_setting_index', 'Dashboard'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'tour')->checkbox(); ?>
    <?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>

    <strong><?= Yii::t('AdminModule.views_setting_index', 'Friendship'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

    <hr>

    <?= CHtml::submitButton(Yii::t('AdminModule.views_setting_index', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

    <!-- show flash message after saving -->
    <?php \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>