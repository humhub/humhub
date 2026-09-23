<?php

use humhub\helpers\Html;
use humhub\modules\content\assets\ContentContainerAsset;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\widgets\Icon;
use yii\helpers\Url;

/**
 * @var Content $content
 * @var bool $state
 * @var Module $module
 */

ContentContainerAsset::register($this);

?>
<li>
    <?= Html::a(
        Icon::get('bell-off') . ' ' . Yii::t('ContentModule.base', 'Turn off notifications'),
        Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 0]),
        [
            'id' => "notification_off_$content->id",
            'class' => array_merge(['dropdown-item ', 'turnOffNotifications'], $state ? [] : ['d-none']),
            'data' => [
                'action-click' => 'content.container.turnOffNotifications',
                'action-url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 0]),
                'content-id' => $content->id,
            ],
        ],
    ) ?>

    <?= Html::a(
        Icon::get('bell') . ' ' . Yii::t('ContentModule.base', 'Turn on notifications'),
        Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 1]),
        [
            'id' => "notification_on_$content->id",
            'class' => array_merge(['dropdown-item ', 'turnOnNotifications'], $state ? ['d-none'] : []),
            'data' => [
                'action-click' => 'content.container.turnOnNotifications',
                'action-url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 1]),
                'content-id' => $content->id,
            ],
        ],
    ) ?>
</li>
