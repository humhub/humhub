<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\ProfileFieldCategory;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_userprofile_index', 'Manage profile attributes'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.views_userprofile_index', 'Here you can create or edit profile categories and fields.'); ?>
    </div>
    <br>

    <div class="pull-right">
        <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_userprofile_index', 'Add new category'), Url::to(['edit-category']), ['class' => 'btn btn-success']); ?>
        <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_userprofile_index', 'Add new field'), Url::to(['edit-field']), ['class' => 'btn btn-success']); ?>
    </div>

    <ul>
        <?php foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category): ?>
            <li>
                <a href="<?= Url::to(['edit-category', 'id' => $category->id]); ?>"><strong><?= Html::encode($category->title); ?></strong></a>
                <ul class="admin-userprofiles-fields">
                    <?php foreach ($category->fields as $field) : ?>
                        <li class="admin-userprofiles-field" data-id="<?= $field->id ?>">
                            <a href="<?= Url::to(['edit-field', 'id' => $field->id]); ?>"><?= Html::encode($field->title); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
