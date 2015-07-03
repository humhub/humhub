<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use humhub\modules\space\models\Space;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> spaces'); ?></div>
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li class="active"><a
                    href="<?php echo Url::toRoute('index'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Overview'); ?></a>
            </li>
            <li>
                <a href="<?php echo Url::toRoute('settings'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Settings'); ?></a>
            </li>
        </ul>
        <p />

        <p>
            <?php echo Yii::t('AdminModule.views_space_index', 'In this overview you can find every space and manage it.'); ?>
        </p>


        <?php
        $visibilities = array(
            Space::VISIBILITY_NONE => Yii::t('SpaceModule.base', 'Private (Invisible)'),
            Space::VISIBILITY_REGISTERED_ONLY => Yii::t('SpaceModule.base', 'Public (Visible)'),
            Space::VISIBILITY_ALL => 'All',
        );

        $joinPolicies = array(
            Space::JOIN_POLICY_NONE => Yii::t('SpaceModule.base', 'Only by invite'),
            Space::JOIN_POLICY_APPLICATION => Yii::t('SpaceModule.base', 'Invite and request'),
            Space::JOIN_POLICY_FREE => 'Everyone can enter',
        );



        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'id',
                    'options' => ['width' => '40px'],
                    'format' => 'raw',
                    'value' => function($data) {
                return $data->id;
            },
                ],
                'name',
                [
                    'attribute' => 'visibility',
                    'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'visibility', array_merge(['' => ''], $visibilities)),
                    'options' => ['width' => '40px'],
                    'format' => 'raw',
                    'value' => function($data) use ($visibilities) {
                if (isset($visibilities[$data->visibility]))
                    return $visibilities[$data->visibility];
                return Html::encode($data->visibility);
            },
                ],
                [
                    'attribute' => 'join_policy',
                    'options' => ['width' => '40px'],
                    'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'join_policy', array_merge(['' => ''], $joinPolicies)),
                    'format' => 'raw',
                    'value' => function($data) use ($joinPolicies) {
                if (isset($joinPolicies[$data->join_policy]))
                    return $joinPolicies[$data->join_policy];
                return Html::encode($data->join_policy);
            },
                ],
                [
                    'header' => 'Actions',
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['width' => '80px'],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return Html::a('<i class="fa fa-eye"></i>', $model->getUrl(), ['class' => 'btn btn-primary btn-xs tt']);
                        },
                                'update' => function($url, $model) {
                            return Html::a('<i class="fa fa-pencil"></i>', $model->createUrl('/space/admin/edit'), ['class' => 'btn btn-primary btn-xs tt']);
                        },
                                'delete' => function($url, $model) {
                            return Html::a('<i class="fa fa-times"></i>', Url::toRoute(['delete', 'id' => $model->id]), ['class' => 'btn btn-danger btn-xs tt']);
                        }
                            ],
                        ],
                    ],
                ]);
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
