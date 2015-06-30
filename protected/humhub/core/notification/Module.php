<?php

namespace humhub\core\notification;

/**
 * NotificationModule
 *
 * @package humhub.modules_core.notification
 * @since 0.5
 */
class Module extends \yii\base\Module
{

    /**
     * Formatted the notification content before delivery
     *
     * @param string $text
     */
    public static function formatOutput($text)
    {
        //$text = HHtml::translateMentioning($text, false);
        //$text = HHtml::translateEmojis($text, false);

        return $text;
    }

}
