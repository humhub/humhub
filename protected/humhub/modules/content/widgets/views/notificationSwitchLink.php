<?php
    use yii\helpers\Url;

?>
<li>
    <?php
    $offLinkId = 'notification_off_' . $content->id;
    $onLinkId = 'notification_on_' . $content->id;

    echo \humhub\widgets\AjaxButton::widget([
        'tag' => 'a',
        'label' => '<i class="fa fa-bell-slash-o"></i> ' . Yii::t('ContentModule.widgets_views_notificationSwitchLink', 'Turn off notifications'),
        'ajaxOptions' => [
            'type' => 'POST',
            'success' => "function(res){ if (res.success) { $('#" . $offLinkId . "').hide(); $('#" . $onLinkId . "').show(); } }",
            'url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 0]),
        ],
        'htmlOptions' => [
            'style' => 'display: ' . ($state ? 'block' : 'none'),
            'href' => '#',
            'id' => $offLinkId
        ]
    ]);

    echo \humhub\widgets\AjaxButton::widget([
        'tag' => 'a',
        'label' => '<i class="fa fa-bell-o"></i> ' . Yii::t('ContentModule.widgets_views_notificationSwitchLink', 'Turn on notifications'),
        'ajaxOptions' => [
            'type' => 'POST',
            'success' => "function(res){ if (res.success) { $('#" . $onLinkId . "').hide(); $('#" . $offLinkId . "').show(); } }",
            'url' => Url::to(['/content/content/notification-switch', 'id' => $content->id, 'switch' => 1]),
        ],
        'htmlOptions' => [
            'style' => 'display: ' . ($state ? 'none' : 'block'),
            'href' => '#',
            'id' => $onLinkId
        ]
    ]);
    ?>
</li>
