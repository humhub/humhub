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
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Displays a membership button between the current and given user.
 *
 * @author luke
 */
class FriendshipButton extends Widget
{
    /**
     * Presentation variant used by the people directory.
     */
    public const VARIANT_DIRECTORY = 'directory';

    /**
     * Presentation variant used when no variant is given.
     */
    public const VARIANT_DEFAULT = 'default';

    /**
     * Option sets which may be selected by name. Since the button is re-rendered after a
     * friendship change, the client only passes the variant name back to the server,
     * which keeps the actual option values under server control.
     *
     * @var array<string, array>
     * @see registerVariant()
     */
    private static $variants = [
        self::VARIANT_DEFAULT => [],
        self::VARIANT_DIRECTORY => [
            'friends' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
            'addFriend' => ['attrs' => ['class' => 'btn btn-accent btn-sm']],
            'acceptFriendRequest' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent'], 'togglerClass' => 'btn btn-sm btn-outline-accent'],
            'cancelFriendRequest' => ['attrs' => ['class' => 'btn btn-sm btn-outline-accent']],
        ],
    ];

    /**
     * @var User the target user
     */
    public $user;

    /**
     * @var string Name of the presentation variant, see [[registerVariant()]]. Unknown
     * names fall back to [[VARIANT_DEFAULT]].
     * @since 1.19
     */
    public $variant = self::VARIANT_DEFAULT;

    /**
     * @var array Options buttons
     */
    public $options = [];

    /**
     * Registers an option set which can be selected by name through [[$variant]].
     *
     * Use this instead of passing options which have to survive the re-render after a
     * friendship change, since options are no longer round tripped through the client.
     *
     * @since 1.19
     */
    public static function registerVariant(string $name, array $options): void
    {
        self::$variants[$name] = $options;
    }

    /**
     * @return string[] names of all registered variants
     * @since 1.19
     */
    public static function getVariantNames(): array
    {
        return array_keys(self::$variants);
    }

    /**
     * Maps a variant name to a registered variant. Unknown or malformed values, e.g. when
     * supplied by a request, resolve to [[VARIANT_DEFAULT]].
     *
     * @param mixed $variant
     * @since 1.19
     */
    public static function resolveVariant($variant): string
    {
        return is_string($variant) && isset(self::$variants[$variant]) ? $variant : self::VARIANT_DEFAULT;
    }

    /**
     * @return array the options of the given variant
     * @since 1.19
     */
    public static function getVariantOptions($variant): array
    {
        return self::$variants[static::resolveVariant($variant)];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->variant = static::resolveVariant($this->variant);

        parent::init();
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
                    'data-button-variant' => $this->variant,
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
                    'data-button-variant' => $this->variant,
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
                    'data-button-variant' => $this->variant,
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
                    'data-button-variant' => $this->variant,
                ],
            ],
            'cancelFriendRequest' => [
                'title' => Icon::get('clock-o') . Yii::t('FriendshipModule.base', 'Pending'),
                'attrs' => [
                    'data-action-click' => 'content.container.relationship',
                    'data-action-url' => Url::to(['/friendship/request/delete', 'userId' => $this->user->id]),
                    'data-action-confirm' => Yii::t('FriendshipModule.base', 'Would you like to withdraw your friendship request?'),
                    'data-button-variant' => $this->variant,
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
            // Effective precedence: defaults < variant < setDefaultOptions() < $this->options
            $defaultOptions = ArrayHelper::merge(
                $this->getDefaultOptions(),
                static::getVariantOptions($this->variant),
            );
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
