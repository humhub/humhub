<?php

use humhub\modules\topic\widgets\TopicPicker;
use yii\helpers\Html;
use humhub\modules\content\assets\ContentFormAsset;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\UserPickerField;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\UploadProgress;
use humhub\widgets\Link;
use humhub\widgets\Button;

/* @var $defaultVisibility integer */
/* @var $submitUrl string */
/* @var $form string */
/* @var $submitButtonText string */
/* @var $fileHandlers \humhub\modules\file\handler\BaseFileHandler[] */
/* @var $canSwitchVisibility boolean */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */

ContentFormAsset::register($this);

$this->registerJsConfig('content.form', [
    'defaultVisibility' => $defaultVisibility,
    'disabled' => ($contentContainer instanceof Space && $contentContainer->isArchived()),
    'text' => [
        'makePrivate' => Yii::t('ContentModule.base', 'Make private'),
        'makePublic' => Yii::t('ContentModule.base', 'Make public'),
        'info.archived' => Yii::t('ContentModule.base', 'This space is archived.')
]]);

$pickerUrl = ($contentContainer instanceof Space) ? $contentContainer->createUrl('/space/membership/search') : null;

?>

<div class="panel panel-default clearfix">
    <div class="panel-body" id="contentFormBody" style="display:none;" data-action-component="content.form.CreateForm" >
        <?= Html::beginForm($submitUrl, 'POST'); ?>

        <?= $form; ?>

        <div id="notifyUserContainer" class="form-group" style="margin-top: 15px;display:none;">
            <?= UserPickerField::widget([
                'id' => 'notifyUserInput',
                'url' => $pickerUrl,
                'formName' => 'notifyUserInput',
                'maxSelection' => 10,
                'disabledItems' => [Yii::$app->user->guid],
                'placeholder' => Yii::t('ContentModule.base', 'Add a member to notify'),
            ]) ?>
        </div>

        <div id="postTopicContainer" class="form-group" style="margin-top: 15px;display:none;">
            <?= TopicPicker::widget([
                    'id' => 'postTopicInput',
                    'name' => 'postTopicInput',
                    'contentContainer' => $contentContainer
            ]); ?>
        </div>

        <?= Html::hiddenInput("containerGuid", $contentContainer->guid); ?>
        <?= Html::hiddenInput("containerClass", get_class($contentContainer)); ?>

        <ul id="contentFormError"></ul>

        <div class="contentForm_options">
            <hr>
            <div class="btn_container">
                <?= Button::info($submitButtonText)->action('submit')->id('post_submit_button')->submit() ?>

                <?php
                $uploadButton = UploadButton::widget([
                            'id' => 'contentFormFiles',
                            'progress' => '#contentFormFiles_progress',
                            'preview' => '#contentFormFiles_preview',
                            'dropZone' => '#contentFormBody',
                            'max' => Yii::$app->getModule('content')->maxAttachedFiles
                ]);
                ?>
                <?= FileHandlerButtonDropdown::widget(['primaryButton' => $uploadButton, 'handlers' => $fileHandlers, 'cssButtonClass' => 'btn-default']); ?>

                <!-- public checkbox -->
                <?= Html::checkbox("visibility", "", ['id' => 'contentForm_visibility', 'class' => 'contentForm hidden', 'aria-hidden' => 'true', 'title' => Yii::t('ContentModule.base', 'Content visibility') ]); ?>

                <!-- content sharing -->
                <div class="pull-right">

                    <span class="label label-info label-public hidden"><?= Yii::t('ContentModule.base', 'Public'); ?></span>

                    <ul class="nav nav-pills preferences" style="right: 0; top: 5px;">
                        <li class="dropdown">
                            <a class="dropdown-toggle" style="padding: 5px 10px;" data-toggle="dropdown" href="#" aria-label="<?= Yii::t('base', 'Toggle post menu'); ?>" aria-haspopup="true">
                                <i class="fa fa-cogs"></i></a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <?= Link::withAction(Yii::t('ContentModule.base', 'Notify members'), 'notifyUser')->icon('fa-bell')?>
                                </li>
                                 <li>
                                     <?= Link::withAction(Yii::t('ContentModule.base', 'Topics'), 'setTopics')->icon(Yii::$app->getModule('topic')->icon) ?>
                                </li>
                                <?php if ($canSwitchVisibility): ?>
                                    <li>
                                        <?= Link::withAction(Yii::t('ContentModule.base', 'Make public'), 'changeVisibility')
                                            ->id('contentForm_visibility_entry')->icon('fa-unlock') ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <?= UploadProgress::widget(['id' => 'contentFormFiles_progress']) ?>
            <?= FilePreview::widget(['id' => 'contentFormFiles_preview', 'edit' => true, 'options' => ['style' => 'margin-top:10px;']]); ?>

        </div>
        <!-- /contentForm_Options -->
        <?= Html::endForm(); ?>
    </div>
    <!-- /panel body -->
</div> <!-- /panel -->