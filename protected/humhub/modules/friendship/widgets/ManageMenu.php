<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\friendship\models\Friendship;

/**
 * Account Settings Tab Menu
 */
class ManageMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $friendCount = Friendship::getFriendsQuery($this->user)->count();
        $this->addItem([
            'label' => Yii::t('FriendshipModule.base', 'Friends') . ' (' . $friendCount . ')',
            'url' => Url::toRoute(['/friendship/manage/list']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->id == 'manage' && Yii::$app->controller->action->id == 'list'),
        ]);

        $receivedRequestsCount = Friendship::getReceivedRequestsQuery($this->user)->count();
        $this->addItem([
            'label' => Yii::t('FriendshipModule.base', 'Requests') . ' (' . $receivedRequestsCount . ')',
            'url' => Url::toRoute(['/friendship/manage/requests']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->id == 'manage' && Yii::$app->controller->action->id == 'requests'),
        ]);

        $sentRequestsCount = Friendship::getSentRequestsQuery($this->user)->count();
        $this->addItem([
            'label' => Yii::t('FriendshipModule.base', 'Sent requests') . ' (' . $sentRequestsCount . ')',
            'url' => Url::toRoute(['/friendship/manage/sent-requests']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->id == 'manage' && Yii::$app->controller->action->id == 'sent-requests'),
        ]);

        parent::init();
    }

}
