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
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\topic\permissions\ManageTopics;
use humhub\modules\topic\widgets\ContentTopicButton;
use humhub\modules\topic\widgets\TopicPicker;
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

        if ($record->content->canEdit() && TopicPicker::showTopicPicker($record->content->container)) {
            $event->sender->addWidget(ContentTopicButton::class, ['record' => $record], ['sortOrder' => 370]);
        }
    }

    /**
     * @param $event
     */
    public static function onSpaceSettingMenuInit($event)
    {
        /* @var DefaultMenu $menu */
        $menu = $event->sender;

        if ($menu->space->isAdmin() && $menu->space->can(ManageTopics::class) && Yii::$app->getModule('space')->settings->get('allowSpaceTopics', true)) {
            $menu->addEntry(new MenuLink([
                'label' => Yii::t('TopicModule.base', 'Topics'),
                'url' => $menu->space->createUrl('/topic/manage'),
                'isActive' => ControllerHelper::isActivePath('topic', 'manage'),
                'sortOrder' => 250,
            ]));
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

        /* @var AccountMenu $menu */

        $menu->addEntry(new MenuLink([
            'label' => Yii::t('TopicModule.base', 'Topics'),
            'url' => Yii::$app->user->identity->createUrl('/topic/manage'),
            'isActive' => ControllerHelper::isActivePath('topic', 'manage'),
            'sortOrder' => 250,
        ]));

        if (ControllerHelper::isActivePath('topic', 'manage')) {
            AccountMenu::markAsActive('account-settings-settings');
        }
    }
}
