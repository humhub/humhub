<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> spaces'); ?></div>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('AdminModule.views_space_index', 'In this overview you can find every space and manage him.'); ?>
        </p>


        <?php
        $visibilities = array(
            0 => 'Invisible',
            1 => 'Registered Users',
            3 => 'All',
        );

        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'space-grid',
            'dataProvider' => $model->resetScope()->search(),
            'filter' => $model,
            'itemsCssClass' => 'table table-hover',
            /* 'loadingCssClass' => 'loader', */
            'columns' => array(
                array(
                    'value' => 'CHtml::image($data->profileImage->getUrl())',
                    'type' => 'raw',
                    'htmlOptions' => array('class' => 'img-rounded', 'style' => 'width: 24px; height: 24px;'),
                ),
                array(
                    'name' => 'name',
                    'filter' => CHtml::activeTextField($model, 'name', array('placeholder' => Yii::t('AdminModule.views_space_index', 'Search for space name'))),
                    'header' => Yii::t('AdminModule.views_space_index', 'Space name'),
                ),
                array(
                    'name' => 'visibility',
                    'filter' => array("" => Yii::t('AdminModule.views_space_index', 'All'), 0 => Yii::t('AdminModule.views_space_index', 'Invisible'), 1 => Yii::t('AdminModule.views_space_index', 'Registrated only'), 2 => Yii::t('AdminModule.views_space_index', 'All')),
                    'value' => function ($data, $row) {
                            if ($data->visibility == Space::VISIBILITY_NONE)
                                return Yii::t('AdminModule.views_space_index', 'Invisible');
                            else if ($data->visibility == Space::VISIBILITY_REGISTERED_ONLY)
                                return Yii::t('AdminModule.views_space_index', 'Registrated only');
                            else if ($data->visibility == Space::VISIBILITY_ALL)
                                return Yii::t('AdminModule.views_space_index', 'Visible');

                            return $data->visibility;
                        }
                ),
                array(
                    'name' => 'join_policy',
                    'filter' => array("" => Yii::t('AdminModule.views_space_index', 'All'), 0 => Yii::t('AdminModule.views_space_index', 'By Invite'), 1 => Yii::t('AdminModule.views_space_index', 'Invite / Request'), 2 => Yii::t('AdminModule.views_space_index', 'Everbody')),
                    'value' => function ($data, $row) {
                            if ($data->join_policy == Space::JOIN_POLICY_NONE)
                                return Yii::t('AdminModule.views_space_index', 'By invite');
                            else if ($data->join_policy == Space::JOIN_POLICY_APPLICATION)
                                return Yii::t('AdminModule.views_space_index', 'Invite & Request');
                            else if ($data->join_policy == Space::JOIN_POLICY_FREE)
                                return Yii::t('AdminModule.views_space_index', 'Free');

                            return $data->join_policy;
                        }
                ),
                array(
                    'name' => 'ownerUsernameSearch',
                    'header' => Yii::t('AdminModule.views_space_index', 'Space owner'),
                    'filter' => CHtml::activeTextField($model, 'ownerUsernameSearch', array('placeholder' => Yii::t('AdminModule.views_space_index', 'Search for space owner'))),
                    'value' => function ($data, $row) {
                            if (!$data->owner)
                                return "-";

                            return $data->owner->username;
                        }
                ),
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{view}{update}{deleteOwn}',
                    'viewButtonUrl' => 'Yii::app()->createUrl("//space/space", array("sguid"=>$data->guid));',
                    'updateButtonUrl' => 'Yii::app()->createUrl("//space/admin/edit", array("sguid"=>$data->guid));',

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
                                'data-original-title' => Yii::t('AdminModule.views_space_index', 'View space'),
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
                                'data-original-title' => Yii::t('AdminModule.views_space_index', 'Edit space'),
                            ),
                        ),
                        'deleteOwn' => array
                        (
                            'label' => '<i class="fa fa-times"></i>',
                            'imageUrl' => false,
                            'url' => 'Yii::app()->createUrl("//space/admin/delete", array("sguid"=>$data->guid));',
                            'deleteConfirmation' => false,
                            'options' => array(
                                'class' => 'btn btn-danger btn-xs tt',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top',
                                'title' => '',
                                'data-original-title' => Yii::t('AdminModule.views_space_index', 'Delete space'),
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

<script type="text/javascript">
    $(document).ready(function () {
        $('.grid-view-loading').show();
        $('.grid-view-loading').css('display', 'block !important');
        $('.grid-view-loading').css('opacity', '1 !important');
    });

</script>
