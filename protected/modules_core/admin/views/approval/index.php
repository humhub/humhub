<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_approval_index', '<strong>Pending</strong> user approvals'); ?></div>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('AdminModule.views_approval_index', 'Here you see all users who have registered and still waiting for a approval.'); ?>
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
                    'htmlOptions' => array('width' => '30px'),
                ),
                array(
                    'name' => 'username',
                    'header' => 'Username',
                    'filter' => CHtml::activeTextField($model, 'username', array('placeholder' => Yii::t('AdminModule.views_approval_index', 'Search for username'))),
                ),
                array(
                    'name' => 'group_id',
                    'value' => 'Group::getGroupNameById($data->group_id)',
                    'filter' => GroupAdmin::gridItems(),
                ),
                array(
                    'name' => 'email',
                    'header' => Yii::t('AdminModule.views_approval_index', 'Email'),
                    'filter' => CHtml::activeTextField($model, 'email', array('placeholder' => Yii::t('AdminModule.views_approval_index', 'Search for email'))),
                ),
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{accept}{decline}',
                    'htmlOptions' => array('width' => '160px'),
                    'buttons' => array
                    (
                        'accept' => array
                        (
                            'label' => Yii::t('AdminModule.views_approval', 'Accept'),
                            'url' => 'Yii::app()->createUrl("admin/approval/approveUserAccept", array("id" => $data->id))',
                            'imageUrl' => false,
                            'options' => array(
                                'style' => 'margin-right: 3px',
                                'class' => 'btn btn-primary btn-sm',
                            ),
                        ),
                        'decline' => array
                        (
                            'label' => Yii::t('AdminModule.views_approval', 'Decline'),
                            'url' => 'Yii::app()->createUrl("admin/approval/approveUserDecline", array("id" => $data->id))',
                            'imageUrl' => false,
                            'options' => array(
                                'style' => 'margin-right: 3px',
                                'class' => 'btn btn-danger btn-sm',
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

    </div>
</div>

