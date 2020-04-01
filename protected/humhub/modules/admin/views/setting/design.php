<?php

use humhub\libs\LogoImage;
use humhub\modules\admin\models\forms\DesignSettingsForm;
use humhub\modules\web\pwa\widgets\SiteIcon;
use humhub\widgets\Button;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use humhub\compat\CHtml;

/* @var $model DesignSettingsForm */

\humhub\modules\admin\assets\AdminAsset::register($this);

$this->registerJsConfig('admin', [
    'text' => [
        'confirm.deleteLogo.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> image deletion'),
        'confirm.deleteLogo.body' => Yii::t('UserModule.account', 'Do you really want to delete your logo image?'),
        'confirm.deleteLogo.confirm' => Yii::t('AdminModule.settings', 'Delete'),
        'confirm.deleteIcon.header' => Yii::t('AdminModule.settings', '<strong>Confirm</strong> icon deletion'),
        'confirm.deleteIcon.body' => Yii::t('UserModule.account', 'Do you really want to delete your icon image?'),
        'confirm.deleteIcon.confirm' => Yii::t('AdminModule.settings', 'Delete')
    ]
]);

$iconUrl = SiteIcon::getUrl(140);

?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'Appearance Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.settings', 'These settings refer to the appearance of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'theme')->dropDownList($model->getThemes()); ?>

    <?= $form->field($model, 'paginationSize'); ?>

    <?= $form->field($model, 'displayName')->dropDownList(['{username}' => Yii::t('AdminModule.settings', 'Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.settings', 'Firstname Lastname (e.g. John Doe)')]); ?>

    <?= $form->field($model, 'spaceOrder')->dropDownList(['0' => Yii::t('AdminModule.settings', 'Alphabetical'), '1' => Yii::t('AdminModule.settings', 'Last visit')]); ?>

    <?= $form->field($model, 'dateInputDisplayFormat')->dropDownList([
        '' => Yii::t('AdminModule.settings', 'Auto format based on user language - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'short')]),
        'php:d/m/Y' => Yii::t('AdminModule.settings', 'Fixed format (dd/mm/yyyy) - Example: {example}', ['{example}' => Yii::$app->formatter->asDate(time(), 'php:d/m/Y')]),
    ]);
    ?>
    <strong><?= Yii::t('AdminModule.settings', 'Mobile appearance'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'horImageScrollOnMobile')->checkbox(); ?>
    <?= $form->field($model, 'useDefaultSwipeOnMobile')->checkbox(); ?>


    <div class="well">
        <?= $form->field($model, 'logo')->fileInput(['id' => 'admin-logo-file-upload', 'data-action-change' => 'admin.changeLogo', 'style' => 'display: none', 'name' => 'logo[]']); ?>
        <div class="image-upload-container" id="logo-upload">
            <?php if (LogoImage::hasImage()): ?>
                <img class="img-rounded" id="logo-image" src="<?= LogoImage::getUrl() ?>"
                     data-src="holder.js/140x140"
                     alt="<?= Yii::t('AdminModule.settings', "You're using no logo at the moment. Upload your logo now."); ?>"
                     style="max-height: 40px;">
            <?php endif; ?>

            <div class="image-upload-buttons" id="logo-upload-buttons" style="display: block;">
                <?= Button::info()->icon('cloud-upload')->id('admin-logo-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-logo-image')
                    ->action('admin.deletePageIcon', Url::to(['/admin/setting/delete-logo-image']))
                    ->style(LogoImage::hasImage() ? '' : 'display:none')->icon('times')->sm()->loader(false) ?>
            </div>
        </div>
    </div>

    <div class="well">
        <?= $form->field($model, 'icon')->fileInput(['id' => 'admin-icon-file-upload', 'data-action-change' => 'admin.changeIcon', 'style' => 'display: none', 'name' => 'icon[]']); ?>
        <div class="image-upload-container" id="icon-upload">
            <img class="img-rounded" id="icon-image" src="<?= $iconUrl ?>"
                 alt="<?= Yii::t('AdminModule.settings', "You're using no icon at the moment. Upload your logo now."); ?>"
                 style="max-height: 40px;">

            <div class="image-upload-buttons" id="icon-upload-buttons" style="display: block;">
                <?= Button::info()->icon('cloud-upload')->id('admin-icon-upload-button')->sm()->loader(false) ?>

                <?= Button::danger()->id('admin-delete-icon-image')
                    ->action('admin.deletePageIcon', Url::to(['/admin/setting/delete-icon-image']))
                    ->style(SiteIcon::hasImage() ? '' : 'display:none')->icon('times')->sm()->loader(false) ?>
            </div>
        </div>
    </div>

    <hr>
    <?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?= \humhub\widgets\DataSaved::widget(); ?>
    <?php ActiveForm::end(); ?>
</div>
