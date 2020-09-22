<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use humhub\modules\friendship\models\Friendship;

/**
 * Account Settings Tab Menu
 */
class ManageMenu extends TabMenu
{

    /**
     * @var \humhub\modules\user\models\User
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
            'isActive' => MenuLink::isActiveState(null, 'manage', 'list')
        ]));

        $receivedRequestsCount = Friendship::getReceivedRequestsQuery($this->user)->count();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('FriendshipModule.base', 'Requests') . ' (' . $receivedRequestsCount . ')',
            'url' => ['/friendship/manage/requests'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState(null, 'manage', 'requests')
        ]));

        $sentRequestsCount = Friendship::getSentRequestsQuery($this->user)->count();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('FriendshipModule.base', 'Sent requests') . ' (' . $sentRequestsCount . ')',
            'url' => ['/friendship/manage/sent-requests'],
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState(null, 'manage', 'sent-requests')
        ]));

        parent::init();
    }

}
