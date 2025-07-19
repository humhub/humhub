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
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountSettingsMenu;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\GridView;
use humhub\widgets\modal\ModalButton;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $addModel Topic */
/* @var $title string */
?>


<div class="panel panel-default">
    <div class="panel-heading"><?= $title ?></div>

    <?php
    if ($contentContainer instanceof Space) {
        echo DefaultMenu::widget(['space' => $contentContainer]);
    } elseif ($contentContainer instanceof User) {
        echo AccountSettingsMenu::widget();
    }
    ?>

    <div class="panel-body">
        <?php if (Topic::isAllowedToCreate($contentContainer)) : ?>
            <?php $form = ActiveForm::begin(); ?>
            <p><?= Yii::t('TopicModule.base', 'Add topics that you will use in your posts. Topics can be personal interests or general terms. When posting, you can select them by choosing "Topics" and it will be easier for other users to find your posts related to that topic.') ?></p>
            <div class="mb-3">
                <div class="input-group">
                    <?= Html::activeTextInput($addModel, 'name', ['class' => 'form-control', 'placeholder' => Yii::t('TopicModule.base', 'Add Topic')]) ?>
                    <?= Button::light()->icon('add')->loader()->submit() ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        <?php endif; ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                'name',
                'sort_order',
                [
                    'header' => Yii::t('base', 'Actions'),
                    'class' => ActionColumn::class,
                    'options' => ['width' => '100px'],
                    'buttons' => [
                        'update' => function ($url, $model) use ($contentContainer) {
                            /* @var $model Topic */
                            return ModalButton::primary()->load($contentContainer->createUrl('edit', ['id' => $model->id]))->icon('edit')->sm()->loader(false);
                        },
                        'view' => function ($url, $model) use ($contentContainer) {
                            /* @var $model Topic */
                            return Button::primary()->link($model->getUrl())->icon('fa-filter')->sm()->loader(false);
                        },
                        'delete' => function ($url, $model) use ($contentContainer) {
                            /* @var $model Topic */
                            return Button::danger()->icon('delete')->action('topic.removeOverviewTopic', $contentContainer->createUrl('delete', ['id' => $model->id]))->confirm(
                                Yii::t('TopicModule.base', '<strong>Confirm</strong> topic deletion'),
                                Yii::t('TopicModule.base', 'Do you really want to delete this topic?'),
                                Yii::t('base', 'Delete')
                            )->sm()->loader(false);
                        },
                    ],
                ],
            ]]);
        ?>
    </div>
</div>
