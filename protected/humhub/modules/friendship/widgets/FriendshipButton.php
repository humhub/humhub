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
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Displays a membership button between the current and given user.
 *
 * @author luke
 */
class FriendshipButton extends Widget
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
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to end your friendship with {userName}?', ['{userName}' => '<strong>' . $this->user->getDisplayName() . '</strong>']),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info active',
                ],
            ],
            'addFriend' => [
                'title' => '<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Friends'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/add', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to send a friendship request to {userName}?', ['{userName}' => '<strong>' . $this->user->getDisplayName() . '</strong>']),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info',
                ],
            ],
            'acceptFriendRequest' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Accept Friend Request'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/add', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to accept the friendship request?'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info active',
                ],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-info active',
            ],
            'denyFriendRequest' => [
                'title' => '<span class="fa fa-times"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Deny friend request'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw the friendship request?'),
                    'data-button-options' => Json::encode($this->options),
                ],
            ],
            'cancelFriendRequest' => [
                'title' => '<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Pending'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw your friendship request?'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-info active',
                ],
            ],
        ];
    }

    public function setDefaultOptions(array $defaultOptions)
    {
        $this->options = $this->getOptions($defaultOptions);
    }

    public function getOptions(array $defaultOptions = null): array
    {
        if ($defaultOptions === null) {
            $defaultOptions = $this->getDefaultOptions();
        }

        return ArrayHelper::merge($defaultOptions, $this->options);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!self::isVisibleForUser($this->user)) {
            return '';
        }

        return $this->render('friendshipButton', [
            'user' => $this->user,
            'friendshipState' => Friendship::getStateForUser(Yii::$app->user->getIdentity(), $this->user),
            'options' => $this->getOptions(),
        ]);
    }

    public static function isVisibleForUser(User $user): bool
    {
        return !Yii::$app->user->isGuest
            && Yii::$app->getModule('friendship')->isFriendshipEnabled()
            && !$user->isCurrentUser();
    }

}
