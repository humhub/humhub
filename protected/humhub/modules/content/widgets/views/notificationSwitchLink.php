<?php

use humhub\libs\Html;
use humhub\modules\content\assets\ContentContainerAsset;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
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
        '<i class="fa fa-bell-slash-o"></i> ' . Yii::t('ContentModule.base', 'Turn off notifications'),
        Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 0]),
        [
            'id' => "notification_off_$content->id",
            'class' => ['turnOffNotifications'],
            'style' => [
                'display' => $state ? 'block' : 'none',
            ],
            'data' => [
                'action-click' => 'content.container.turnOffNotifications',
                'action-url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 0]),
                'content-id' => $content->id,
            ]
        ]
    ); ?>

    <?= Html::a(
        '<i class="fa fa-bell-o"></i> ' . Yii::t('ContentModule.base', 'Turn on notifications'),
        Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 1]),
        [
            'id' => "notification_on_$content->id",
            'class' => ['turnOnNotifications'],
            'style' => [
                'display' => $state ? 'none' : 'block',
            ],
            'data' => [
                'action-click' => 'content.container.turnOnNotifications',
                'action-url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 1]),
                'content-id' => $content->id,
            ]
        ]
    ); ?>
</li>
