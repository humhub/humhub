<?php

use humhub\modules\space\models\Space;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_listTypes', '<strong>Manage</strong> space types'); ?></div>
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li><a
                    href="<?php echo Url::toRoute('index'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Overview'); ?></a>
            </li>
            <li class="active">
                <a href="<?php echo Url::toRoute('list-types'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Types'); ?></a>
            </li>            
            <li>
                <a href="<?php echo Url::toRoute('settings'); ?>"><?php echo Yii::t('AdminModule.views_space_index', 'Settings'); ?></a>
            </li>
        </ul>
        <p class="pull-right">
            <?php echo Html::a(Yii::t('AdminModule.views_space_listTypes', "Create new type"), Url::toRoute('edit-type'), array('class' => 'btn btn-primary')); ?>
        </p>
        <br>
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                'id',
                'title',
                'item_title',
              [
                    'attribute' => 'show_in_directory',
                    'value' =>
                    function($data) {
                return ($data->show_in_directory == 1) ? 'Yes' : 'No';
            }
                ],
                'sort_key',
                [
                    'header' => 'Actions',
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['width' => '80px'],
                    'buttons' => [
                        'update' => function($url, $model) {
                            return Html::a('<i class="fa fa-pencil"></i>', Url::to(['edit-type', 'id' => $model->id]), ['class' => 'btn btn-primary btn-xs tt']);
                        },
                                'view' => function() {
                            return;
                        },
                                'delete' => function() {
                            return;
                        },
                            ],
                        ],
                ]]);
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
