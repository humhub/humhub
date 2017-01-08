<?php
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
/* @var $form yii\widgets\ActiveForm */

use yii\bootstrap\Html
?>
<div class="table-responsive">
    <table class="table table-middle table-hover">
        <thead>
            <tr>
                <th><?= Yii::t('NotificationModule.widgets_views_notificationSettingsForm', 'Category') ?></th>
                <th><?= Yii::t('NotificationModule.widgets_views_notificationSettingsForm', 'Description') ?></th>
                <?php foreach ($model->targets($user) as $target): ?>
                    <th class="text-center">
                        <?= $target->getTitle(); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->categories($user) as $category): ?>
                <tr>
                    <td>
                        <strong><?= $category->getTitle() ?></strong>
                    </td>
                    <td>
                        <?= $category->getDescription() ?>
                    </td>

                    <?php foreach ($model->targets($user) as $target): ?>
                        <td class="text-center">
                            <label style="margin:0px;">
                                <?= Html::checkbox($model->getSettingFormname($category, $target), $target->isCategoryEnabled($category), ['style' => 'margin:0px;']) ?>
                            </label>
                        </td>
                    <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>

