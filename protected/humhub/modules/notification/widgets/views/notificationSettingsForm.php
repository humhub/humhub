<?php
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
/* @var $form yii\widgets\ActiveForm */

use yii\bootstrap\Html
?>

<br />
<?= $form->field($model, 'desktopNotifications')->checkbox(); ?>

<?php if($showSpaces) : ?>
    <?= humhub\modules\space\widgets\SpacePickerField::widget([
        'form' => $form,
        'model' => $model,
        'attribute' => 'spaceGuids',
        'defaultResults' => $defaultSpaces,
        'maxSelection' => 10
    ])?>
<?php endif;?>

<div class="grid-view table-responsive permission-grid-editor" style="padding-top:0px;">
    <table class="table table-middle table-hover">
        <thead>
            <tr>
                <th><?= Yii::t('NotificationModule.widgets_views_notificationSettingsForm', 'Type') ?></th>
                <?php foreach ($model->targets() as $target): ?>
                    <th class="text-center">
                        <?= $target->getTitle(); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->categories() as $category): ?>
                <tr>
                    <td>
                        <strong><?= $category->getTitle() ?></strong><br />
                        <?= $category->getDescription() ?>
                    </td>
                    <?php foreach ($model->targets() as $target): ?>
                        <td class="text-center">
                            <?php $disabled = !$target->isEditable($model->user) || $category->isFixedSetting($target) ?>
                            <?= Html::checkbox($model->getSettingFormname($category, $target), $target->isCategoryEnabled($category, $model->user), ['style' => 'margin:0px;', 'disabled' => $disabled]) ?>
                        </td>
                    <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>

