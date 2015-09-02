<li>
    <?php
    $offLinkId = 'notification_off_' . $content->getUniqueId();
    $onLinkId = 'notification_on_' . $content->getUniqueId();

    echo HHtml::ajaxLink('<i class="fa fa-bell-slash-o"></i> '.Yii::t('WallModule.widgets_views_notificationSwitchLink', 'Turn off notifications'), Yii::app()->createUrl('//wall/content/notificationSwitch', array(
                'id' => $content->id,
                'className' => get_class($content),
                'switch' => 0
            )), array(
        'dataType' => 'json',
        'type' => 'POST',
        'data' => array(Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
        'success' => "js:function(res){ if (res.success) { $('#" . $offLinkId . "').hide(); $('#" . $onLinkId . "').show(); } }"
            ), array(
        'style' => 'display: ' . ($state ? 'block' : 'none'),
        'id' => $offLinkId
    ));
    ?>
    <?php
    echo HHtml::ajaxLink('<i class="fa fa-bell-o"></i> '.Yii::t('WallModule.widgets_views_notificationSwitchLink', 'Turn on notifications'), Yii::app()->createUrl('//wall/content/notificationSwitch', array(
                'id' => $content->id,
                'className' => get_class($content),
                'switch' => 1
            )), array(
        'dataType' => 'json',
        'type' => 'POST',
        'data' => array(Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
        'success' => "js:function(res){ if (res.success) { $('#" . $onLinkId . "').hide(); $('#" . $offLinkId . "').show(); } }"
            ), array(
        'style' => 'display: ' . ($state ? 'none' : 'block'),
        'id' => 'notification_on_' . $content->getUniqueId()
    ));
    ?>
</li>
