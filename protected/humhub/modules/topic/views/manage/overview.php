<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\widgets\ModalButton;
use yii\bootstrap\ActiveForm;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountSettingsMenu;
use yii\helpers\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $addModel \humhub\modules\topic\models\Topic */
/* @var $title string */
?>


<div class="panel panel-default">
    <div class="panel-heading"><?= $title ?></div>

    <?php if ($contentContainer instanceof Space) : ?>
        <?= DefaultMenu::widget(['space' => $contentContainer]); ?>
    <?php elseif ($contentContainer instanceof User) : ?>
        <?= AccountSettingsMenu::widget() ?>
    <?php endif; ?>

    <div class="panel-body">

        <?php $form = ActiveForm::begin(); ?>
        <p><?= Yii::t('TopicModule.base', 'Add topics that you will use in your posts. Topics can be personal interests or general terms. When posting, you can select them by choosing "Topics" and it will be easier for other users to find your posts related to that topic.') ?></p>
        <div class="form-group">
            <div class="input-group">
                <?= Html::activeTextInput($addModel, 'name', ['style' => 'height:36px', 'class' => 'form-control', 'placeholder' => Yii::t('TopicModule.base', 'Add Topic')]) ?>
                <span class="input-group-btn">
                    <?= Button::defaultType()->icon('add')->loader()->submit() ?>
                </span>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                'name',
                'sort_order',
                [
                    'header' => Yii::t('base', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['width' => '80px'],
                    'buttons' => [
                        'update' => function ($url, $model) use ($contentContainer) {
                            /* @var $model \humhub\modules\topic\models\Topic */
                            return ModalButton::primary()->load($contentContainer->createUrl('edit', ['id' => $model->id]))->icon('edit')->xs()->loader(false);
                        },
                        'view' => function ($url, $model) use ($contentContainer) {
                            /* @var $model \humhub\modules\topic\models\Topic */
                            return Button::primary()->link($model->getUrl())->icon('fa-filter')->xs()->loader(false);
                        },
                        'delete' => function ($url, $model) use ($contentContainer) {
                            /* @var $model \humhub\modules\topic\models\Topic */
                            return Button::danger()->icon('delete')->action('topic.removeOverviewTopic', $contentContainer->createUrl('delete', ['id' => $model->id]))->confirm(
                                Yii::t('TopicModule.base', '<strong>Confirm</strong> topic deletion'),
                                Yii::t('TopicModule.base', 'Do you really want to delete this topic?'),
                                Yii::t('base', 'Delete'))->xs()->loader(false);
                        },
                    ],
                ],
            ]]);
        ?>
    </div>
</div>
