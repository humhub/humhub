<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\assets\NotificationVueAsset;
use humhub\widgets\Icon;
use humhub\widgets\VueComponent;
use yii\helpers\Url;

/* @var array $initial the first page of notifications, see NotificationListService */
/* @var array $filters [{id, title}] the notification categories that can be filtered by */

// The page content is one island (NotificationOverview): the page header, the filter in the
// sidebar and the list in the main panel - see the component's own docblock.
?>
<div class="container">
    <?= VueComponent::widget([
        'name' => 'NotificationOverview',
        'assetBundle' => NotificationVueAsset::class,
        'props' => [
            'initial' => $initial,
            'filters' => $filters,
            'pageSize' => humhub\modules\notification\services\NotificationListService::OVERVIEW_PAGE_SIZE,
            'settingsUrl' => Url::to(['/notification/user']),
            'icons' => [
                'all' => Icon::get('menu-2')->asString(),
                'unseen' => Icon::get('eye-off')->asString(),
                'seen' => Icon::get('eye')->asString(),
            ],
        ],
    ]) ?>
</div>
