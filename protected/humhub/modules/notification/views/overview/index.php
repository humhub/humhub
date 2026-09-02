<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\assets\NotificationVueAsset;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\VueComponent;
use yii\helpers\Url;

/* @var array $initial the first page of notifications, see NotificationWindowService */
/* @var array $categories [{id, title}] the categories that can be filtered by */

// The page content is one island (NotificationOverview): the filter in the sidebar and the list
// in the main panel are one piece of state, so they share an owner. It renders the panel/column
// markup this view used to hold - see the component's own docblock.
?>
<div class="container">
    <?= VueComponent::widget([
        'name' => 'NotificationOverview',
        'assetBundle' => NotificationVueAsset::class,
        'props' => [
            'initial' => $initial,
            'categories' => $categories,
            'pageSize' => humhub\modules\notification\services\NotificationWindowService::OVERVIEW_PAGE_SIZE,
            'settingsUrl' => Url::to(['/notification/user']),
            'icons' => [
                'check' => Icon::get('check')->asString(),
                'cog' => Icon::get('cog')->asString(),
                'all' => Icon::get('bars')->asString(),
                'unseen' => Icon::get('eye-slash')->asString(),
                'seen' => Icon::get('eye')->asString(),
            ],
        ],
    ]) ?>
</div>
