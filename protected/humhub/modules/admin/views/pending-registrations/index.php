<?php

use humhub\widgets\Button;
use humhub\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.base', 'Pending user registrations'); ?></h4>

    <div class="help-block">
        <?= Yii::t('AdminModule.views_approval_index', 'The following list contains all pending sign-ups and invites.'); ?>
    </div>

    <div class="dropdown pull-right">
        <button class="btn btn-primary btn-sm " type="button" data-toggle="dropdown"><i class="fa fa-download"></i> <?= Yii::t('base', 'Export')?> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><?= Button::asLink('csv', Url::current(['export' => '1', 'format' => 'CSV']))->pjax(false)->icon('fa-file-code-o')->sm() ?></li>
                <li><?= Button::asLink('xlsx', Url::current(['export' => '1', 'format' => 'XLSX']))->pjax(false)->icon('fa-file-excel-o')->sm() ?></li>
            </ul>
    </div>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => ['email',
            'originator.username',
            'language',
            'created_at',
            [
                'attribute' => 'source',
                'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'source', array_merge(['' => ''], $types)),
                'options' => ['width' => '40px'],
                'format' => 'raw',
                'value' => function($data) use ($types) {
                    if (isset($types[$data->source])) {
                        return $types[$data->source];
                    }
                    return Html::encode($data->source);
                },
            ],]
    ]);
    ?>

</div>