<?php

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\modules\admin\grid\CheckboxColumn;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Url;

/* @var $category ProfileFieldCategory */

?>


<?= Button::success(Yii::t('AdminModule.user', 'Add new field'))
    ->icon('plus')->sm()->link(Url::to(['edit-field', 'categoryId' => $category->id]))->right()->style('margin-left:5px') ?>

<?= Button::primary(Yii::t('AdminModule.user', 'Edit category'))
    ->icon('pencil')->sm()->link(Url::to(['edit-category', 'id' => $category->id]))->right() ?>


<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider(['allModels' => $category->fields]),
    'layout' => '{items}',
    'columns' => [
        [
            'attribute' => 'title',
        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Required'),
            'attribute' => 'required',

        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Visible'),
            'attribute' => 'visible',

        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Editable'),
            'attribute' => 'editable',

        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Searchable'),
            'attribute' => 'searchable',

        ],
        [
            'header' => Yii::t('base', 'Actions'),
            'class' => ActionColumn::class,
            'options' => ['style' => 'width:56px;'],
            'contentOptions' => ['style' => 'text-align:center'],
            'headerOptions' => ['style' => 'text-align:center'],
            'buttons' => [
                'view' => function () {
                    return;
                },
                'delete' => function ($url, $model) {
                    return;
                },
                'update' => function ($url, $category) {
                    /* @var $model ProfileField */
                    return Button::primary()->icon('pencil')->link(Url::to(['edit-field', 'id' => $category->id]))->sm();
                },
            ],
        ],
    ]
]);
