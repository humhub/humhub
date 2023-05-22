<?php

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use humhub\modules\admin\grid\CheckboxColumn;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\helpers\Html;

/* @var $category ProfileFieldCategory */

?>


<?= Button::success(Yii::t('AdminModule.user', 'Add new field'))
    ->icon('add')->sm()->link(Url::to(['edit-field', 'categoryId' => $category->id]))->right()->style('margin-left:5px') ?>

<?= Button::primary(Yii::t('AdminModule.user', 'Edit category'))
    ->icon('edit')->sm()->link(Url::to(['edit-category', 'id' => $category->id]))->right() ?>

    <br/>

<?= GridView::widget([
    'dataProvider' => new ArrayDataProvider(['allModels' => $category->fields, 'pagination' => ['pageSize' => 0]]),
    'layout' => '{items}',
    'columns' => [
        [
            'attribute' => 'title',
            'content' => function (ProfileField $model, $key, $index, $that) {
                return Html::encode(Yii::t($model->getTranslationCategory(), $that->getDataCellValue($model, $key, $index)));
            }
        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Required'),
            'attribute' => 'required',
            'options' => ['style' => 'width: fit-content;'],
            'headerOptions' => ['style' => 'word-break: keep-all; hyphens: none;'],
            'content' => function (ProfileField $model, $key, $index, $that) {
                if ($model->getFieldType()->isVirtual) {
                    return '';
                }
                return $that->getDataCellValue($model, $key, $index);
            }
        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Visible'),
            'options' => ['style' => 'width: fit-content;'],
            'headerOptions' => ['style' => 'word-break: keep-all; hyphens: none'],
            'attribute' => 'visible',
        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Editable'),
            'options' => ['style' => 'width: fit-content;'],
            'headerOptions' => ['style' => 'word-break: keep-all; hyphens: none'],
            'attribute' => 'editable',
            'content' => function (ProfileField $model, $key, $index, $that) {
                if ($model->getFieldType()->isVirtual) {
                    return '';
                }
                return $that->getDataCellValue($model, $key, $index);
            }
        ],
        [
            'class' => CheckboxColumn::class,
            'label' => Yii::t('UserModule.profile', 'Searchable'),
            'options' => ['style' => 'width: fit-content;'],
            'headerOptions' => ['style' => 'word-break: keep-all; hyphens: none'],
            'attribute' => 'searchable',
            'content' => function (ProfileField $model, $key, $index, $that) {
                if ($model->getFieldType()->isVirtual) {
                    return '';
                }
                return $that->getDataCellValue($model, $key, $index);
            }
        ],
        [
            'attribute' => 'sort_order',
        ],
        [
            'header' => '&nbsp;',
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
                    return Button::primary()->icon('edit')->link(Url::to(['edit-field', 'id' => $category->id]))->sm();
                },
            ],
        ],
    ]
]);
