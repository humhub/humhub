<?php

use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\widgets\bootstrap\Tabs;
use yii\helpers\Url;

$categoryItems = [];
foreach (ProfileFieldCategory::find()->orderBy('sort_order')->all() as $category) {
    $categoryItems[] = [
        'label' => $category->title,
        'encode' => true,
        'params' => ['category' => $category],
        'view' => '_fieldGrid'
    ];
}

$categoryItems[] = [
    'label' => Icon::get('add', [
        'htmlOptions' => [
            'title' => Yii::t('AdminModule.user', 'Add new category'),
            'class' => 'tt'
        ],
    ]),
    'encode' => false,
    'url' => Url::to(['edit-category'])
];
?>

<div class="panel-body">

    <h4><?= Yii::t('AdminModule.user', 'Manage profile attributes') ?></h4>
    <div class="text-body-secondary">
        <?= Yii::t('AdminModule.user', 'Here you can create or edit profile categories and fields.'); ?>
    </div>

    <?= Tabs::widget([
        'viewPath' => '@admin/views/user-profile/',
        'isSubMenu' => true,
        'items' => $categoryItems,
    ]) ?>
</div>
