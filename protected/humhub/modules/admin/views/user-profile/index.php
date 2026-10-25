<?php

use humhub\modules\user\models\ProfileFieldCategory;
use humhub\widgets\bootstrap\Tabs;
use humhub\widgets\ContentCard;
use humhub\widgets\Icon;
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
    'label' => Icon::get('add')->tooltip(Yii::t('AdminModule.user', 'Add new category')),
    'encode' => false,
    'url' => Url::to(['edit-category'])
];
?>

<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.user', 'Profiles'),
]) ?>
<?= Tabs::widget([
    'viewPath' => '@admin/views/user-profile/',
    'isSubMenu' => true,
    'items' => $categoryItems,
]) ?>
<?php ContentCard::end() ?>
