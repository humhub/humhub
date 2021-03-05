<?php

use humhub\modules\file\widgets\Upload;
use \humhub\modules\xcoin\models\Tag;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\compat\CHtml;


/** @var $allSpacesTag Tag */
/** @var $allUsersTag Tag */

\humhub\modules\admin\assets\AdminAsset::register($this);

$ast_upload = Upload::create();
$aut_upload = Upload::create();
?>

<h4><?= Yii::t('AdminModule.views_tag_all', 'Overview'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.views_tag_all', 'This overview contains the default all space/users cover images.'); ?>
</div>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<div class="row">
    <div class="col-md-12">
        <label class="control-label"><?= Yii::t('AdminModule.views_tag_all', 'All Spaces Tag Cover') ?></label><br>
        <br>
        <?= $ast_upload->progress() ?>
    </div>
</div>

<div class="well">
    <?= $ast_upload->button([
        'submitName' => 'ast-cover',
        'label' => true,
        'tooltip' => false,
        'options' => ['accept' => 'image/*'],
        'cssButtonClass' => 'btn-default btn-sm',
        'dropZone' => '#tag-form',
        'max' => 1,
    ]) ?>
    <?= $ast_upload->preview([
        'options' => ['style' => 'margin-top:10px'],
        'showInStream' => true,
    ]) ?>

    <div class="image-upload-container" id="logo-upload">
        <img id="logo-image" src="<?= $allSpacesTag ? $allSpacesTag->getCover()->getUrl() : '' ?>" data-src="holder.js/140x140"
             alt="<?= Yii::t('AdminModule.views_tag_all', "No All Space Tag Cover at the moment. Upload your cover now."); ?>"
             style="max-height: 40px; margin-top: 12px;">
    </div>
</div>

<br>
<br>

<div class="row">
    <div class="col-md-12">
        <label class="control-label"><?= Yii::t('AdminModule.views_tag_all', 'All Spaces Tag Cover') ?></label><br>
        <br>
        <?= $aut_upload->progress() ?>
    </div>
</div>

<div class="well">
    <?= $aut_upload->button([
        'submitName' => 'aut-cover',
        'label' => true,
        'tooltip' => false,
        'options' => ['accept' => 'image/*'],
        'cssButtonClass' => 'btn-default btn-sm',
        'dropZone' => '#tag-form',
        'max' => 1,
    ]) ?>
    <?= $aut_upload->preview([
        'options' => ['style' => 'margin-top:10px'],
        'showInStream' => true,
    ]) ?>

    <div class="image-upload-container" id="logo-upload">
        <img id="logo-image" src="<?= $allUsersTag ? $allUsersTag->getCover()->getUrl() : '' ?>" data-src="holder.js/140x140"
             alt="<?= Yii::t('AdminModule.views_tag_all', "No All Space Tag Cover at the moment. Upload your cover now."); ?>"
             style="max-height: 40px; margin-top: 12px;">
    </div>
</div>

<br>

<?= CHtml::submitButton(Yii::t('AdminModule.views_tag_all', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php ActiveForm::end(); ?>

