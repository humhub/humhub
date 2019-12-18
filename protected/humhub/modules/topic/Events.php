<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\ContentTopicButton;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\widgets\AccountMenu;
use Yii;
use yii\base\BaseObject;

class Events extends BaseObject
{
    public static function onWallEntryControlsInit($event)
    {
        /** @var ContentActiveRecord $record */
        $record = $event->sender->object;

        if ($record->content->canEdit()) {
            $event->sender->addWidget(ContentTopicButton::class, ['record' => $record], ['sortOrder' => 370]);
        }
    }

    /**
     * @param $event
     */
    public static function onSpaceSettingMenuInit($event)
    {
        $space = $event->sender->space;

        if ($space->isAdmin()) {
            $event->sender->addItem([
                'label' => Yii::t('TopicModule.base', 'Topics'),
                'url' => $space->createUrl('/topic/manage'),
                'isActive' => MenuLink::isActiveState('topic', 'manage'),
                'sortOrder' => 250
            ]);
        }
    }

    /**
     * @param $event UserEvent
     */
    public static function onProfileSettingMenuInit($event)
    {
        if(Yii::$app->user->isGuest) {
            return;
        }

        $event->sender->addItem([
            'label' => Yii::t('TopicModule.base', 'Topics'),
            'url' => Yii::$app->user->identity->createUrl('/topic/manage'),
            'isActive' => MenuLink::isActiveState('topic', 'manage'),
            'sortOrder' => 250
        ]);

        if(MenuLink::isActiveState('topic', 'manage')) {
            AccountMenu::markAsActive('account-settings-settings');
        }
    }
}
