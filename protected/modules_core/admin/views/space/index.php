<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> spaces'); ?></div>
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li class="active"><a
                    href="<?php echo $this->createUrl('index'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Overview'); ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('settings'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Settings'); ?></a>
            </li>
        </ul>
        <p />

        <p>
            <?php echo Yii::t('AdminModule.views_space_index', 'In this overview you can find every space and manage it.'); ?>
        </p>


        <?php
        $visibilities = array(
            0 => Yii::t('SpaceModule.base', 'Private (Invisible)'),
            1 => Yii::t('SpaceModule.base', 'Public (Visible)'),
                /* 3 => 'All', */
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
                    'filter' => array("" => Yii::t('AdminModule.views_space_index', 'All'), 0 => Yii::t('SpaceModule.base', 'Private (Invisible)'), 1 => Yii::t('SpaceModule.base', 'Public (Visible)')/* , 2 => Yii::t('AdminModule.views_space_index', 'All') */),
                    'value' => function ($data, $row) {
                if ($data->visibility == Space::VISIBILITY_NONE)
                    return Yii::t('SpaceModule.base', 'Private (Invisible)');
                else if ($data->visibility == Space::VISIBILITY_REGISTERED_ONLY)
                    return Yii::t('SpaceModule.base', 'Public (Visible)');
                else if ($data->visibility == Space::VISIBILITY_ALL)
                    return '(no longer supported)';

                return $data->visibility;
            }
                ),
                array(
                    'name' => 'join_policy',
                    'filter' => array("" => Yii::t('AdminModule.views_space_index', 'All'), 0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')),
                    'value' => function ($data, $row) {
                if ($data->join_policy == Space::JOIN_POLICY_NONE)
                    return Yii::t('SpaceModule.base', 'Only by invite');
                else if ($data->join_policy == Space::JOIN_POLICY_APPLICATION)
                    return Yii::t('SpaceModule.base', 'Invite and request');
                else if ($data->join_policy == Space::JOIN_POLICY_FREE)
                    return Yii::t('SpaceModule.base', 'Everyone can enter');

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
    $(document).ready(function() {
        $('.grid-view-loading').show();
        $('.grid-view-loading').css('display', 'block !important');
        $('.grid-view-loading').css('opacity', '1 !important');
    });

</script>
