<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
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
     * Buttons which may be addressed by request supplied options.
     */
    private const REQUEST_BUTTONS = [
        'friends',
        'addFriend',
        'acceptFriendRequest',
        'denyFriendRequest',
        'cancelFriendRequest',
    ];

    /**
     * Presentation only options which may be supplied by the request. Everything else —
     * titles and all `data-action-*` attributes — is server generated and must not be
     * overridable, since the rendered button is inserted as markup by the client.
     */
    private const REQUEST_OPTIONS = ['groupClass', 'togglerClass'];

    /**
     * @var User the target user
     */
    public $user;

    /**
     * @var array Options buttons
     */
    public $options = [];

    /**
     * Reduces request supplied button options to a set of harmless presentation options.
     *
     * The button is re-rendered after a friendship change and has to keep the presentation of
     * the context it was rendered in, so the options make a round trip through the client.
     * Only the presentation state that actually needs to survive that round trip is accepted.
     *
     * @param mixed $options JSON encoded or already decoded button options
     * @return array the sanitized options
     * @since 1.18.5
     */
    public static function sanitizeRequestOptions($options): array
    {
        if (is_string($options)) {
            try {
                $options = Json::decode($options);
            } catch (InvalidArgumentException $e) {
                return [];
            }
        }

        if (!is_array($options)) {
            return [];
        }

        $sanitized = [];

        foreach ($options as $button => $config) {
            if (!in_array($button, self::REQUEST_BUTTONS, true) || !is_array($config)) {
                continue;
            }

            foreach ($config as $option => $value) {
                if (in_array($option, self::REQUEST_OPTIONS, true)) {
                    $sanitized[$button][$option] = static::sanitizeCssClass($value);
                }
            }

            if (isset($config['attrs']['class'])) {
                $sanitized[$button]['attrs']['class'] = static::sanitizeCssClass($config['attrs']['class']);
            }
        }

        return $sanitized;
    }

    private static function sanitizeCssClass($value): string
    {
        return is_string($value) ? preg_replace('/[^\w \-]/', '', $value) : '';
    }

    private function getDefaultOptions()
    {
        return [
            'friends' => [
                'title' => Icon::get('check') . Yii::t('FriendshipModule.base', 'Friends'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to end your friendship with {userName}?', ['{userName}' => '<strong>' . $this->user->getDisplayName() . '</strong>']),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent active',
                ],
            ],
            'addFriend' => [
                'title' => Icon::get('plus') . Yii::t('FriendshipModule.base', 'Friends'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/add', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to send a friendship request to {userName}?', ['{userName}' => '<strong>' . $this->user->getDisplayName() . '</strong>']),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent',
                ],
            ],
            'acceptFriendRequest' => [
                'title' => Icon::get('clock-o') . Yii::t('FriendshipModule.base', 'Accept Friend Request'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/add', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to accept the friendship request?'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent active',
                ],
                'groupClass' => 'btn-group',
                'togglerClass' => 'btn btn-accent active',
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
                'title' => Icon::get('clock-o') . Yii::t('FriendshipModule.base', 'Pending'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw your friendship request?'),
                    'data-button-options' => Json::encode($this->options),
                    'data-ui-loader' => '',
                    'class' => 'btn btn-accent active',
                ],
            ],
        ];
    }

    public function setDefaultOptions(array $defaultOptions)
    {
        $this->options = $this->getOptions($defaultOptions);
    }

    public function getOptions(?array $defaultOptions = null): array
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
