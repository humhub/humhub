<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\User;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;

/**
 * Account Settings Tab Menu
 */
class ManageMenu extends TabMenu
{
    /**
     * @var User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $friendCount = Friendship::getFriendsQuery($this->user)->count();

        $this->addEntry(new MenuLink([
            'label' => Yii::t('FriendshipModule.base', 'Friends') . ' (' . $friendCount . ')',
            'url' => ['/friendship/manage/list'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath(null, 'manage', 'list'),
        ]));

        $receivedRequestsCount = Friendship::getReceivedRequestsQuery($this->user)->count();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('FriendshipModule.base', 'Requests') . ' (' . $receivedRequestsCount . ')',
            'url' => ['/friendship/manage/requests'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath(null, 'manage', 'requests'),
        ]));

        $sentRequestsCount = Friendship::getSentRequestsQuery($this->user)->count();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('FriendshipModule.base', 'Sent requests') . ' (' . $sentRequestsCount . ')',
            'url' => ['/friendship/manage/sent-requests'],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath(null, 'manage', 'sent-requests'),
        ]));

        parent::init();
    }

}
