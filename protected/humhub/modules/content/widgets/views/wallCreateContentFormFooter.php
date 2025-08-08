<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\UploadProgress;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;

/* @var $submitUrl string */
/* @var $submitButtonText string */
/* @var $fileHandlers BaseFileHandler[] */
/* @var $canSwitchVisibility bool */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $fileList array */
/* @var $isModal bool */
/* @var $pickerUrl string */
/* @var $scheduleUrl string */
?>

<div class="notifyUserContainer my-3 d-none">
    <?= UserPickerField::widget([
        'id' => 'notifyUserInput' . ($isModal ? 'Modal' : ''),
        'url' => $pickerUrl,
        'formName' => 'notifyUserInput',
        'maxSelection' => 10,
        'disabledItems' => [Yii::$app->user->guid],
        'placeholder' => Yii::t('ContentModule.base', 'Add a member to notify'),
    ]) ?>
</div>

<div id="postTopicContainer<?= $isModal ? 'Modal' : '' ?>" class="my-3 d-none">
    <?= TopicPicker::widget([
        'id' => 'postTopicInput' . ($isModal ? 'Modal' : ''),
        'name' => 'postTopicInput',
        'contentContainer' => $contentContainer,
    ]) ?>
</div>

<?= Html::hiddenInput('containerGuid', $contentContainer->guid) ?>
<?= Html::hiddenInput('containerClass', $contentContainer::class) ?>

<div class="contentForm_options">
    <hr>
    <div class="btn_container">
        <?= Button::info($submitButtonText)->action('submit', $submitUrl)->id('post_submit_button' . ($isModal ? '_modal' : ''))->submit() ?>

        <?php $uploadButton = UploadButton::widget([
            'id' => 'contentFormFiles' . ($isModal ? 'Modal' : ''),
            'tooltip' => Yii::t('ContentModule.base', 'Attach Files'),
            'progress' => '#contentFormFiles_progress' . ($isModal ? 'Modal' : ''),
            'preview' => '#contentFormFiles_preview' . ($isModal ? 'Modal' : ''),
            'dropZone' => '#contentFormBody' . ($isModal ? 'Modal' : ''),
            'max' => Yii::$app->getModule('content')->maxAttachedFiles,
            'fileList' => $fileList,
        ]); ?>
        <?= FileHandlerButtonDropdown::widget(['primaryButton' => $uploadButton, 'handlers' => $fileHandlers, 'cssButtonClass' => 'btn-light']); ?>

        <!-- public checkbox -->
        <?= Html::checkbox('visibility', '', ['class' => 'contentForm_visibility contentForm d-none', 'aria-hidden' => 'true']); ?>

        <!-- state data -->
        <?= Html::hiddenInput('state', Content::STATE_PUBLISHED) ?>

        <!-- content sharing -->
        <div class="float-end">
            <span class="badge-container">
                <?= Badge::info(Yii::t('ContentModule.base', 'Public'))
                    ->cssClass(['badge-public', 'd-none']) ?>
            </span>

            <ul class="nav nav-pills" style="right:0;top:5px">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" style="padding:5px 10px" data-bs-toggle="dropdown" href="#"
                       aria-label="<?= Yii::t('base', 'Toggle post menu') ?>" aria-haspopup="true">
                        <?= Icon::get('cogs') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <?= Link::withAction(Yii::t('ContentModule.base', 'Notify members'), 'notifyUser')->icon('bell')->cssClass('dropdown-item') ?>
                        </li>
                        <?php if (TopicPicker::showTopicPicker($contentContainer)) : ?>
                            <li>
                                <?= Link::withAction(Yii::t('ContentModule.base', 'Topics'), 'setTopics')->icon(Yii::$app->getModule('topic')->icon)->cssClass('dropdown-item') ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($canSwitchVisibility): ?>
                            <li>
                                <?= Link::withAction(Yii::t('ContentModule.base', 'Change to "Public"'), 'changeVisibility')
                                    ->cssClass('contentForm_visibility_entry dropdown-item')
                                    ->icon('unlock') ?>
                            </li>
                        <?php endif; ?>
                        <li>
                            <?= Link::withAction(Yii::t('ContentModule.base', 'Create as draft'), 'changeState')
                                ->cssClass('dropdown-item')
                                ->icon('edit')
                                ->options([
                                    'data-state' => Content::STATE_DRAFT,
                                    'data-state-title' => Yii::t('ContentModule.base', 'Draft'),
                                    'data-button-title' => Yii::t('ContentModule.base', 'Save as draft'),
                                ]) ?>
                        </li>
                        <?php if (!$isModal): ?>
                            <li>
                                <?= Link::withAction(Yii::t('ContentModule.base', 'Schedule publication'), 'scheduleOptions', $scheduleUrl)
                                    ->cssClass('dropdown-item')
                                    ->icon('clock-o') ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <?= UploadProgress::widget([
        'id' => 'contentFormFiles_progress' . ($isModal ? 'Modal' : ''),
    ]) ?>
    <?= FilePreview::widget([
        'id' => 'contentFormFiles_preview' . ($isModal ? 'Modal' : ''),
        'edit' => true,
        'items' => $fileList,
        'options' => ['style' => 'margin-top:10px;'],
    ]) ?>
</div><!-- /contentForm_Options -->
