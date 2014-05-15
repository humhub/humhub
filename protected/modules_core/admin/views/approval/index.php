<h1><?php echo Yii::t('AdminModule.base', 'Pending user approvals');?></h1>

<p>
    <?php echo Yii::t('AdminModule.base', 'Here you see all users who have registered and still waiting for a approval.');?>
</p>

<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'approve-grid',
    'dataProvider' => $model->resetScope()->searchNeedApproval(),
    'filter' => $model,
    'itemsCssClass' => 'table table-hover',
    /*'loadingCssClass' => 'loader',*/
    'columns' => array(
        array(
            'value' => 'CHtml::image($data->profileImage->getUrl())',
            'type' => 'raw',
        ),
        array(
            'name' => 'username',
            'header' => 'Username',
            'htmlOptions' => array('width' => '300px'),
        ),
        array(
            'name' => 'group_id',
            'value' => 'Group::getGroupNameById($data->group_id)',
            'filter' => GroupAdmin::gridItems(),
        ),
        'email',
        array(
            'class' => 'CButtonColumn',
            'template' => '{view}',
            'viewButtonUrl' => 'Yii::app()->createUrl("admin/approval/approveUser", array("id"=>$data->id));',
            'buttons'=>array
            (
                'view' => array
                (
                    'label'=>'<i class="fa fa-eye"></i>',
                    'imageUrl'=>false,
                    'options' => array(
                        'style' => 'margin-right: 3px',
                        'class' => 'btn btn-primary btn-xs tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => '',
                        'data-original-title' => 'View user approval',
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
