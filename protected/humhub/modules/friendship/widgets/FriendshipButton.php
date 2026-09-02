<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\widgets;

use humhub\modules\friendship\assets\FriendshipVueAsset;
use humhub\modules\friendship\serializers\FriendshipSerializer;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use humhub\widgets\VueComponent;
use Yii;
use yii\base\Widget;

/**
 * The friendship button between the current and the given user.
 *
 * Since 1.20 this widget renders the `FriendshipButton` Vue island (see its own docblock) and
 * hands it the current friendship state; every transition afterwards happens client side
 * against `/api/v2/user/<id>/friendship`. What the widget still owns is what only the server
 * knows: the initial state, the user, the rendered icon markup — and the presentation.
 *
 * ## Presentation
 *
 * `$buttonClass` styles the "add" button, `$stateClass` the three states that follow, and
 * `$togglerClass`/`$groupClass` the dropdown around a received request. They replace the
 * per-button option array of earlier versions; nullable, so an {@see self::EVENT_INIT} handler
 * can supply a default without overriding what the call site configured:
 *
 * ```php
 * $friendshipButton->buttonClass ??= 'btn btn-accent btn-sm';
 * ```
 *
 * @author luke
 */
class FriendshipButton extends Widget
{
    /**
     * @var string classes of the "add friend" button
     */
    public const DEFAULT_BUTTON_CLASS = 'btn btn-accent';

    /**
     * @var string classes of the pending, received-request and friends states
     */
    public const DEFAULT_STATE_CLASS = 'btn btn-accent active';

    /**
     * @var User the target user
     */
    public $user;

    /**
     * @var string|null classes of the "add friend" button
     * @since 1.20
     */
    public ?string $buttonClass = null;

    /**
     * @var string|null classes of the pending, received-request and friends states
     * @since 1.20
     */
    public ?string $stateClass = null;

    /**
     * @var string|null classes of the dropdown toggle next to "Accept Friend Request"
     * @since 1.20
     */
    public ?string $togglerClass = null;

    /**
     * @var string|null classes of the button group of the received-request state
     * @since 1.20
     */
    public ?string $groupClass = null;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!self::isVisibleForUser($this->user)) {
            return '';
        }

        return VueComponent::widget([
            'name' => 'FriendshipButton',
            'assetBundle' => FriendshipVueAsset::class,
            'props' => [
                'userId' => $this->user->id,
                'userName' => $this->user->getDisplayName(),
                'initial' => FriendshipSerializer::state($this->user),
                'buttonClass' => $this->buttonClass ?? self::DEFAULT_BUTTON_CLASS,
                'stateClass' => $this->stateClass ?? self::DEFAULT_STATE_CLASS,
                'togglerClass' => $this->togglerClass ?? self::DEFAULT_STATE_CLASS,
                'groupClass' => $this->groupClass ?? 'btn-group',
                'checkIconHtml' => Icon::get('check')->asString(),
                'plusIconHtml' => Icon::get('plus')->asString(),
                'clockIconHtml' => Icon::get('clock-o')->asString(),
                'timesIconHtml' => Icon::get('times')->asString(),
            ],
        ]);
    }

    public static function isVisibleForUser(User $user): bool
    {
        return !Yii::$app->user->isGuest
            && Yii::$app->getModule('friendship')->isFriendshipEnabled()
            && !$user->isCurrentUser();
    }

}
