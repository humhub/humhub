<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic;

use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\ContentTopicButton;
use Yii;
use yii\base\BaseObject;

class Events extends BaseObject
{
    public static function onWallEntryControlsInit($event)
    {
        $record = $event->sender->object;

        if ($record->content->canWrite()) {
            $event->sender->addWidget(ContentTopicButton::class, ['record' => $record], ['sortOrder' => 240]);
        }
    }

    public static function onSpaceSettingMenuInit($event)
    {
        $space = $event->sender->space;

        if ($space->isAdmin()) {
            $event->sender->addItem([
                'label' => Yii::t('TopicModule.base', 'Topics'),
                'url' => $space->createUrl('/topic/manage'),
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'topic' && Yii::$app->controller->id == 'manage'),
                'sortOrder' => 250
            ]);
        }
    }
}
