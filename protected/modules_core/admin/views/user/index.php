<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_user_index', '<strong>Manage</strong> users'); ?></div>
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li class="active"><a
                    href="<?php echo $this->createUrl('index'); ?>"><?php echo Yii::t('AdminModule.views_user_index', 'Overview'); ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('add'); ?>"><?php echo Yii::t('AdminModule.views_user_index', 'Add new user'); ?></a>
            </li>
        </ul>
        <p />
        <p>
            <?php echo Yii::t('AdminModule.views_user_index', 'In this overview you can find every registered user and manage him.'); ?>
        </p>

        <?php
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'user-grid',
            'dataProvider' => $model->resetScope()->search(),
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
                    'header' => Yii::t('AdminModule.views_user_index', 'Username'),
                    'filter' => CHtml::activeTextField($model, 'username', array('placeholder' => Yii::t('AdminModule.views_user_index', 'Search for username'))),
                ),
                array(
                    'name' => 'email',
                    'header' => Yii::t('AdminModule.views_user_index', 'Email'),
                    'filter' => CHtml::activeTextField($model, 'email', array('placeholder' => Yii::t('AdminModule.views_user_index', 'Search for email'))),
                ),
                array(
                    'name' => 'super_admin',
                    'header' => Yii::t('AdminModule.views_user_index', 'Admin'),
                    'filter' => array("" => Yii::t('AdminModule.views_user_index', 'All'), 0 => Yii::t('AdminModule.views_user_index', 'No'), 1 => Yii::t('AdminModule.views_user_index', 'Yes')),
                ),
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{view}{update}{deleteOwn}',
                    'viewButtonUrl' => 'Yii::app()->createUrl("//user/profile", array("uguid"=>$data->guid));',
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
                                'data-original-title' => Yii::t('AdminModule.views_user_index', 'View user profile'),
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
                                'data-original-title' => Yii::t('AdminModule.views_user_index', 'Edit user account'),
                            ),
                        ),
                        'deleteOwn' => array
                            (
                            'label' => '<i class="fa fa-times"></i>',
                            'visible' => '$data->id != Yii::app()->user->id', //cannot delete yourself
                            'imageUrl' => false,
                            'url' => 'Yii::app()->createUrl("//admin/user/delete", array("id"=>$data->id));',
                            'deleteConfirmation' => false,
                            'options' => array(
                                'class' => 'btn btn-danger btn-xs tt',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top',
                                'title' => '',
                                'data-original-title' => Yii::t('AdminModule.views_user_index', 'Delete user account'),
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