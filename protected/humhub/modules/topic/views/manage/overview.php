<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\topic\models\forms\TopicSettingsForm;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicTable;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/* @var $this View */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $addModel Topic */
/* @var $topicSettings TopicSettingsForm */
/* @var $title string */
?>


<?php if ($contentContainer instanceof User) : ?>
    <?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
<?php else : ?>
    <?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
        'title' => Yii::t('SpaceModule.manage', 'Settings'),
        'tabs' => new DefaultMenu(['space' => $contentContainer]),
    ]) ?>
    <?php ContentCard::begin(['title' => Yii::t('TopicModule.base', 'Topics')]) ?>
<?php endif; ?>
        <?php if (Topic::isAllowedToCreate($contentContainer)) : ?>
            <?php $form = ActiveForm::begin(); ?>
            <p><?= Yii::t('TopicModule.base', 'Add topics that you will use in your posts. Topics can be personal interests or general terms. When posting, you can select them by choosing "Topics" and it will be easier for other users to find your posts related to that topic.') ?></p>
            <div class="mb-3">
                <div class="input-group">
                    <?= Html::activeTextInput($addModel, 'name', ['class' => 'form-control', 'placeholder' => Yii::t('TopicModule.base', 'Add Topic')]) ?>
                    <?= Button::light()
                        ->icon('add')
                        ->options(['aria-label' => Yii::t('TopicModule.base', 'Add Topic')])
                        ->submit() ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>

            <?php if ($contentContainer instanceof Space) : ?>
                <?php $form = ActiveForm::begin() ?>
                    <?= $form->field($topicSettings, 'pickerVisibility')
                        ->dropDownList($topicSettings->getPickerVisibilityOptions(), ['data-action-change' => 'ui.form.submit']) ?>
                <?php ActiveForm::end() ?>
            <?php endif; ?>
        <?php endif; ?>

        <?= TopicTable::widget(['container' => $contentContainer]) ?>
<?php if ($contentContainer instanceof User) : ?>
    <?php $this->endContent() ?>
<?php else : ?>
    <?php ContentCard::end() ?>
    <?php $this->endContent() ?>
<?php endif; ?>
