<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\base\Widget;

/**
 * NotificationSwitch for Wall Entries
 *
 * This widget allows turn on/off of notifications of a content.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
class NotificationSwitchLink extends Widget
{
    /**
     * @var ContentActiveRecord
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('notificationSwitchLink', [
            'content' => $this->content->content,
            'state' => $this->content->isFollowedByUser(Yii::$app->user->id, true),
        ]);
    }

}
