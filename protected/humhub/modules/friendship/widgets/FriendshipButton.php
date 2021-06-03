<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Displays a membership button between the current and given user.
 *
 * @author luke
 */
class FriendshipButton extends \yii\base\Widget
{

    /**
     * @var User the target user
     */
    public $user;

    /**
     * @var array Options buttons
     */
    public $options = [];

    private function getDefaultOptions()
    {
        return [
            'friends' => [
                'title' => '<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Friends'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('FriendshipModule.base', 'Would you like to end your friendship with {userName}?', ['{userName}' => '"' . $this->user->getDisplayName() . '"']),
                    'class' => 'btn btn-info active',
                ],
            ],
            'addFriend' => [
                'title' => '<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Friends'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('FriendshipModule.base', 'Would you like to send a friendship request to {userName}?', ['{userName}' => '"' . $this->user->getDisplayName() . '"']),
                    'class' => 'btn btn-info',
                ],
            ],
            'acceptFriendRequest' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Accept Friend Request'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('FriendshipModule.base', 'Would you like to accept the friendship request?'),
                    'class' => 'btn btn-info active',
                ],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-info active',
            ],
            'denyFriendRequest' => [
                'title' => '<span class="fa fa-times"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Deny friend request'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw the friendship request?'),
                ],
            ],
            'cancelFriendRequest' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Pending'),
                'attrs' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw your friendship request?'),
                    'class' => 'btn btn-info active',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!Yii::$app->getModule('friendship')->getIsEnabled()) {
            return;
        }
        
        // Do not display a buttton if user is it self or guest
        if ($this->user->isCurrentUser() || Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('friendshipButton', [
            'user' => $this->user,
            'friendshipState' => Friendship::getStateForUser(Yii::$app->user->getIdentity(), $this->user),
            'options' => ArrayHelper::merge($this->getDefaultOptions(), $this->options),
        ]);
    }

}
