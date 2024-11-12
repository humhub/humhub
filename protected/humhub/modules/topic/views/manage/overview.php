<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountSettingsMenu;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\widgets\ModalButton;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

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
        $topicsAllowed = !! Yii::$app->getModule('space')->settings->get('allowSpaceTopics', true);
    } elseif ($contentContainer instanceof User) {
        echo AccountSettingsMenu::widget();
        $topicsAllowed = !! Yii::$app->getModule('user')->settings->get('auth.allowUserTopics', true);
    } else {
        $topicsAllowed = false;
    }
    ?>

    <div class="panel-body">
        <?php if ($topicsAllowed) : ?>
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
        <?php endif; ?>

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
                            /* @var $model Topic */
                            return ModalButton::primary()->load($contentContainer->createUrl('edit', ['id' => $model->id]))->icon('edit')->xs()->loader(false);
                        },
                        'view' => function ($url, $model) use ($contentContainer) {
                            /* @var $model Topic */
                            return Button::primary()->link($model->getUrl())->icon('fa-filter')->xs()->loader(false);
                        },
                        'delete' => function ($url, $model) use ($contentContainer) {
                            /* @var $model Topic */
                            return Button::danger()->icon('delete')->action('topic.removeOverviewTopic', $contentContainer->createUrl('delete', ['id' => $model->id]))->confirm(
                                Yii::t('TopicModule.base', '<strong>Confirm</strong> topic deletion'),
                                Yii::t('TopicModule.base', 'Do you really want to delete this topic?'),
                                Yii::t('base', 'Delete')
                            )->xs()->loader(false);
                        },
                    ],
                ],
            ]]);
        ?>
    </div>
</div>
