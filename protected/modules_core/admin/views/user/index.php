<h1><?php echo Yii::t('AdminModule.base', 'Manage users'); ?></h1>

<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'user-grid',
    'dataProvider' => $model->resetScope()->notDeleted()->search(),
    'filter' => $model,
    'itemsCssClass' => 'table table-hover',
    // 'loadingCssClass' => 'loader',
    'columns' => array(
        array(
            'value' => 'CHtml::image($data->profileImage->getUrl())',
            'type' => 'raw',
            'htmlOptions' => array('width' => '30px'),
        ),
        array(
            'name' => 'username',
            'header' => Yii::t('AdminModule.user', 'Username'),
            'filter' => CHtml::activeTextField($model, 'username', array('placeholder' => Yii::t('AdminModule.user', 'Search for username'))),
        ),
        array(
            'name' => 'email',
            'header' => Yii::t('AdminModule.user', 'Email'),
            'filter' => CHtml::activeTextField($model, 'username', array('placeholder' => Yii::t('AdminModule.user', 'Search for email'))),
        ),
        array(
            'name' => 'super_admin',
            'header' => Yii::t('AdminModule.user', 'Admin'),
            'filter' => array("" => Yii::t('AdminModule.user', 'All'), 0 => Yii::t('AdminModule.user', 'No'), 1 => Yii::t('AdminModule.user', 'Yes')),
        ),
        array(
            'class' => 'CButtonColumn',
            'template' => '{view}{update}{deleteOwn}',
            'viewButtonUrl' => 'Yii::app()->createUrl("//user/profile", array("guid"=>$data->guid));',
            'updateButtonUrl' => 'Yii::app()->createUrl("//admin/user/edit", array("id"=>$data->id));',
            'htmlOptions' => array('width' => '90px'),
            'buttons' => array
            (
                'view' => array
                (
                    'label' => '<i class="fa fa-eye"></i>',
                    'imageUrl' => false,
                    'options' => array(
                        'style' => 'margin-right: 3px',
                        'class' => 'btn btn-primary btn-xs tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => '',
                        'data-original-title' => Yii::t('AdminModule.user', 'View user profile'),
                    ),
                ),
                'update' => array
                (
                    'label' => '<i class="fa fa-pencil"></i>',
                    'imageUrl' => false,
                    'options' => array(
                        'style' => 'margin-right: 3px',
                        'class' => 'btn btn-primary btn-xs tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => '',
                        'data-original-title' => Yii::t('AdminModule.user', 'Edit user account'),
                    ),
                ),
                'deleteOwn' => array
                (
                    'label' => '<i class="fa fa-times"></i>',
                    'imageUrl' => false,
                    'url' => 'Yii::app()->createUrl("//admin/user/delete", array("id"=>$data->id));',
                    'deleteConfirmation' => false,
                    'options' => array(
                        'class' => 'btn btn-danger btn-xs tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => '',
                        'data-original-title' => Yii::t('AdminModule.user', 'Delete user account'),
                    ),
                ),
            ),
        ),
    ),
    'pager' => array(
        'class' => 'CLinkPager',
        'maxButtonCount' => 5,
        'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
        'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
        'firstPageLabel' => '<i class="fa fa-fast-backward"></i>',
        'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
        'header' => '',
        'htmlOptions' => array('class' => 'pagination'),
    ),
    'pagerCssClass' => 'pagination-container',
));

?>