<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_index', '<strong>Manage</strong> profiles fields'); ?></div>
    <div class="panel-body">

        <?php echo HHtml::link(Yii::t('AdminModule.views_userprofile_index', 'Add new category'), $this->createUrl('//admin/userprofile/editCategory'), array('class' => 'btn btn-primary')); ?>

        <?php echo HHtml::link(Yii::t('AdminModule.views_userprofile_index', 'Add new field'), $this->createUrl('//admin/userprofile/editField'), array('class' => 'btn btn-primary')); ?>

        <hr>

        <ul>
            <?php foreach (ProfileFieldCategory::model()->findAll(array('order' => 'sort_order')) as $category): ?>
            <li>
                <a href="<?php echo $this->createUrl('editCategory', array('id' => $category->id)); ?>">Category: <?php echo CHtml::encode($category->title); ?></a>
                <ul class="admin-userprofiles-fields">
                    <?php foreach (ProfileField::model()->findAllByAttributes(array('profile_field_category_id' => $category->id), array('order' => 'sort_order')) as $field) : ?>
                        <li class="admin-userprofiles-field" data-id="<?php echo $field->id ?>">
                            <a href="<?php echo $this->createUrl('editField', array('id' => $field->id)); ?>">Field: <?php echo CHtml::encode($field->title); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endforeach; ?>
        </ul>

    </div>
</div>
<?php $this->widget('application.widgets.ReorderContentWidget', array('containerClassName' => 'admin-userprofiles-fields', 'sortableItemClassName' => 'admin-userprofiles-field', 'url' => Yii::app()->createUrl('//admin/userprofile/reorderFields'), 'additionalAjaxParams' => array())); ?>
