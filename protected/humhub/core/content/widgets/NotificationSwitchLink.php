<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\content\widgets;

/**
 * NotificationSwitch for Wall Entries
 *
 * This widget allows turn on/off of notifications of a content.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
class NotificationSwitchLink extends \yii\base\Widget
{

    /**
     * Content Object with SIContentBehaviour
     * @var type
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (\Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('notificationSwitchLink', array(
                    'content' => $this->content,
                    'state' => $this->content->isFollowedByUser(\Yii::$app()->user->id, true)
        ));
    }

}

?>