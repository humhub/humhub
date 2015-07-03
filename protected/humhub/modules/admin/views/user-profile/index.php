<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\modules\user\models\ProfileField;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_index', '<strong>Manage</strong> profiles fields'); ?></div>
    <div class="panel-body">

        <?php echo Html::a(Yii::t('AdminModule.views_userprofile_index', 'Add new category'), Url::to(['edit-category']), array('class' => 'btn btn-primary')); ?>

        <?php echo Html::a(Yii::t('AdminModule.views_userprofile_index', 'Add new field'), Url::to(['edit-field']), array('class' => 'btn btn-primary')); ?>

        <hr>
        <ul>
            <?php foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category): ?>
                <li>
                    <a href="<?php echo Url::to(['edit-category', 'id' => $category->id]); ?>">Category: <?php echo Html::encode($category->title); ?></a>
                    <ul class="admin-userprofiles-fields">
                        <?php foreach ($category->fields as $field) : ?>
                            <li class="admin-userprofiles-field" data-id="<?php echo $field->id ?>">
                                <a href="<?php echo Url::to(['edit-field', 'id' => $field->id]); ?>">Field: <?php echo Html::encode($field->title); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
        </ul>

    </div>
</div>
<?php //$this->widget('application.widgets.ReorderContentWidget', array('containerClassName' => 'admin-userprofiles-fields', 'sortableItemClassName' => 'admin-userprofiles-field', 'url' => Yii::app()->createUrl('//admin/userprofile/reorderFields'), 'additionalAjaxParams' => array()));   ?>
