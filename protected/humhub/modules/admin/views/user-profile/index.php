<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\ProfileFieldCategory;
?>

<h4><?php echo Yii::t('AdminModule.views_userprofile_index', 'Manage profile attributes'); ?></h4>

<br />

<div class="pull-right">
    <?php echo Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_userprofile_index', 'Add new category'), Url::to(['edit-category']), array('class' => 'btn btn-success')); ?>
    <?php echo Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_userprofile_index', 'Add new field'), Url::to(['edit-field']), array('class' => 'btn btn-success')); ?>
</div>

<ul>
    <?php foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category): ?>
        <li>
            <a href="<?php echo Url::to(['edit-category', 'id' => $category->id]); ?>"><strong><?php echo Html::encode($category->title); ?></strong></a>
            <ul class="admin-userprofiles-fields">
                <?php foreach ($category->fields as $field) : ?>
                    <li class="admin-userprofiles-field" data-id="<?php echo $field->id ?>">
                        <a href="<?php echo Url::to(['edit-field', 'id' => $field->id]); ?>"><?php echo Html::encode($field->title); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
</ul>

