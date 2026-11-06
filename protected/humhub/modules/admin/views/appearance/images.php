<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminAsset;
use humhub\modules\admin\models\forms\AppearanceImagesForm;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\mails\MailHeaderImage;

/**
 * @var $this View
 * @var $model AppearanceImagesForm
 */

AdminAsset::register($this);

$img = Yii::$app->img;
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Logo & Images')]) ?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'acknowledge' => true]); ?>

<?= $this->render('_imageUpload', [
    'form' => $form,
    'model' => $model,
    'attribute' => 'logo',
    'key' => 'logo',
    'url' => $img->logo->getUrl(),
    'exists' => $img->logo->exists(),
    'changeAction' => 'admin.changeLogo',
    'deleteAction' => 'admin.deletePageLogo',
    'deleteImage' => 'logo',
]) ?>

<?= $this->render('_imageUpload', [
    'form' => $form,
    'model' => $model,
    'attribute' => 'icon',
    'key' => 'icon',
    'url' => $img->icon->getUrl(['square' => 140]),
    'exists' => $img->icon->exists(),
    'changeAction' => 'admin.changeIcon',
    'deleteAction' => 'admin.deletePageIcon',
    'deleteImage' => 'icon',
]) ?>

<?= $this->render('_imageUpload', [
    'form' => $form,
    'model' => $model,
    'attribute' => 'loginBackgroundImage',
    'key' => 'loginBg',
    'url' => $img->loginBackground->getUrl(),
    'exists' => $img->loginBackground->exists(),
    'changeAction' => 'admin.changeLoginBg',
    'deleteAction' => 'admin.deleteLoginBg',
    'deleteImage' => 'login-background',
]) ?>

<?= $this->render('_imageUpload', [
    'form' => $form,
    'model' => $model,
    'attribute' => 'mailHeaderImage',
    'key' => 'mailHeader',
    'url' => $img->mailHeader->getUrl([
        'maxHeight' => MailHeaderImage::MAX_HEIGHT,
        'maxWidth' => MailHeaderImage::MAX_WIDTH,
    ]),
    'exists' => $img->mailHeader->exists(),
    'changeAction' => 'admin.changeMailHeader',
    'deleteAction' => 'admin.deleteMailHeader',
    'deleteImage' => 'mail-header',
]) ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
