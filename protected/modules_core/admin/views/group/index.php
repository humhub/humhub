<h1><?php echo Yii::t('AdminModule.base', 'Manage groups'); ?></h1>

<?php echo HHtml::link("Create new group", array('//admin/group/edit'), array('class' => 'btn btn-primary')); ?>
<br>


<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'groups-grid',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'itemsCssClass' => 'table table-hover',
    /*'loadingCssClass' => 'loader',*/
    'columns' => array(
        array(
            'name' => 'id',
            'htmlOptions' => array('width' => '40px'),
        ),
        'name',
        'description',
        array(
            'class' => 'CButtonColumn',
            'template' => '{update}',
            'updateButtonUrl' => 'Yii::app()->createUrl("//admin/group/edit", array("id"=>$data->id));',
            'buttons'=>array
            (

                'update' => array
                (
                    'label'=>'<i class="fa fa-pencil"></i>',
                    'imageUrl'=>false,
                    'options' => array(
                        'class' => 'btn btn-primary btn-xs tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => '',
                        'data-original-title' => 'Edit group',
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


