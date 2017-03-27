<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use humhub\compat\CHtml;

\humhub\modules\admin\assets\AdminAsset::register($this);

$this->registerJsConfig('admin', [
    'text' => [
        'confirm.deleteLogo.header' => Yii::t('AdminModule.views_setting_index', '<strong>Confirm</strong> image deletion'),
        'confirm.deleteLogo.body' => Yii::t('UserModule.views_setting_index', 'Do you really want to delete your logo image?'),
        'confirm.deleteLogo.confirm' => Yii::t('AdminModule.views_setting_index', 'Delete')
    ]
]);
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'Appearance Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 'These settings refer to the appearance of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'theme')->dropDownList($themes); ?>

    <?= $form->field($model, 'paginationSize'); ?>

    <?= $form->field($model, 'displayName')->dropDownList(['{username}' => Yii::t('AdminModule.views_setting_design', 'Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.views_setting_design', 'Firstname Lastname (e.g. John Doe)')]); ?>

    <?= $form->field($model, 'spaceOrder')->dropDownList(['0' => Yii::t('AdminModule.views_setting_design', 'Alphabetical'), '1' => Yii::t('AdminModule.views_setting_design', 'Last visit')]); ?>

    <?= $form->field($model, 'dateInputDisplayFormat')->dropDownList([
        '' => Yii::t('AdminModule.views_setting_design', 'Auto format based on user language - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'short')]),
        'php:d/m/Y' => Yii::t('AdminModule.views_setting_design', 'Fixed format (mm/dd/yyyy) - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'php:d/m/Y')]),
    ]);
    ?>
    <strong><?= Yii::t('AdminModule.views_setting_index', 'Wall entry layout'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'horImageScrollOnMobile')->checkbox(); ?>

    <?= $form->field($model, 'logo')->fileInput(['id' => 'admin-logo-file-upload', 'data-action-change' => 'admin.changeLogo', 'style' => 'display: none', 'name' => 'logo[]']); ?>

    <div class="well">
        <div class="image-upload-container" id="logo-upload">
            <img class="img-rounded" id="logo-image" src="<?= ($logo->hasImage()) ? $logo->getUrl() : '' ?>" data-src="holder.js/140x140"
                 alt="<?= Yii::t('AdminModule.views_setting_index', "You're using no logo at the moment. Upload your logo now."); ?>"
                 style="max-height: 40px;">

            <div class="image-upload-buttons" id="logo-upload-buttons" style="display: block;">
                <a id="admin-logo-upload-button" href="#"  class="btn btn-info btn-sm"><i class="fa fa-cloud-upload"></i></a>

                <a id="admin-delete-logo-image" href="#" style="<?= ($logo->hasImage()) ? '' : 'display:none' ?>" class="btn btn-danger btn-sm"
                    data-action-click="admin.deletePageLogo"
                    data-action-url="<?= Url::to(['/admin/setting/delete-logo-image']) ?>" ><i class="fa fa-times"></i></a>
            </div>
        </div>
    </div>

    <hr>
    <?= CHtml::submitButton(Yii::t('AdminModule.views_setting_design', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>