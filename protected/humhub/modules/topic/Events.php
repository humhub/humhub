<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic;

use humhub\helpers\ControllerHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;
use humhub\modules\topic\permissions\ManageTopics;
use humhub\modules\topic\widgets\ContentTopicButton;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\modules\topic\widgets\TopicSidebar;
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

        if ($record->content->canEdit() && TopicPicker::showTopicPicker($record->content->container)) {
            $event->sender->addWidget(ContentTopicButton::class, ['record' => $record], ['sortOrder' => 370]);
        }
    }

    /**
     * @param $event
     */
    public static function onSpaceSettingMenuInit($event)
    {
        $space = $event->sender->space;

        if ($space->isAdmin() && $space->can(ManageTopics::class) && Yii::$app->getModule('space')->settings->get('allowSpaceTopics', true)) {
            $event->sender->addItem([
                'label' => Yii::t('TopicModule.base', 'Topics'),
                'url' => $space->createUrl('/topic/manage'),
                'isActive' => ControllerHelper::isActivePath('topic', 'manage'),
                'sortOrder' => 250,
            ]);
        }
    }

    /**
     * @param $event UserEvent
     */
    public static function onProfileSettingMenuInit($event)
    {
        if (Yii::$app->user->isGuest || !Yii::$app->getModule('user')->settings->get('auth.allowUserTopics', true)) {
            return;
        }

        $event->sender->addItem([
            'label' => Yii::t('TopicModule.base', 'Topics'),
            'url' => Yii::$app->user->identity->createUrl('/topic/manage'),
            'isActive' => ControllerHelper::isActivePath('topic', 'manage'),
            'sortOrder' => 250,
        ]);

        if (ControllerHelper::isActivePath('topic', 'manage')) {
            AccountMenu::markAsActive('account-settings-settings');
        }
    }

    public static function onDashboardSidebarInit($event)
    {
        /* @var DashboardSidebar $sidebar */
        $sidebar = $event->sender;
        $sidebar->addWidget(TopicSidebar::class, [], ['sortOrder' => 100]);
    }

    public static function onSpaceSidebarInit($event)
    {
        /* @var SpaceSidebar $sidebar */
        $sidebar = $event->sender;
        $sidebar->addWidget(
            TopicSidebar::class,
            ['contentContainer' => $sidebar->space],
            ['sortOrder' => 100],
        );
    }
}
