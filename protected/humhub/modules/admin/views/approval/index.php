<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_approval_index', '<strong>Pending</strong> user approvals'); ?></div>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('AdminModule.views_approval_index', 'Here you see all users who have registered and still waiting for a approval.'); ?>
        </p>

        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'group.id',
                    'label' => Yii::t('AdminModule.views_approval_index', 'Group'),
                    'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'group.id', \yii\helpers\ArrayHelper::map($searchModel->getGroups(), 'id', 'name')),
                    'value' =>
                    function($data) {
                        return $data->group->name . " (" . $data->group->id . ")";
                    }
                ], 'username',
                'email',
                'profile.firstname',
                'profile.lastname',
                'profile.lastname',
                [
                    'header' => Yii::t('AdminModule.views_approval_index', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['width' => '150px'],
                    'buttons' => [
                        'view' => function() {
                            return;
                        },
                        'delete' => function($url, $model) {
                            return Html::a(Yii::t('AdminModule.views_approval_index', 'Decline'), Url::toRoute(['decline', 'id' => $model->id]), ['class' => 'btn btn-danger btn-sm']);
                        },
                                'update' => function($url, $model) {
                            return Html::a(Yii::t('AdminModule.views_approval_index', 'Approve'), Url::toRoute(['approve', 'id' => $model->id]), ['class' => 'btn btn-primary btn-sm']);
                        },
                            ],
                        ],
                    ],
                ]);
                ?>

                <?php
                /*
                  $this->widget('zii.widgets.grid.CGridView', array(
                  'id' => 'approve-grid',
                  'dataProvider' => $model->resetScope()->searchNeedApproval(),
                  'filter' => $model,
                  'itemsCssClass' => 'table table-hover',
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
                 *
                 */
                ?>

    </div>
</div>

