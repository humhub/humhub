<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\user\assets\UserVueAsset;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\FollowSerializer;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;
use Yii;

/**
 * The follow button of a user: "Follow" ↔ "Following" (reading "Unfollow" on hover/focus).
 *
 * Since 1.20 this widget renders the `UserFollowButton` Vue island (see its own docblock) with
 * the current follow state inlined; following and unfollowing happen client side against
 * `/api/v2/user/<id>/follow`, and the island keeps itself in sync with the user's
 * `FriendshipButton` through domain events rather than the `.followButton`/`.unfollowButton`
 * pair of links earlier versions rendered.
 *
 * ## Properties kept from earlier versions
 *
 * - `followOptions['class']` / `unfollowOptions['class']` become the classes of the "Follow"
 *   and "Following" states. Every other option (`style`, `data-*`, ...) is ignored: the island
 *   renders the button and talks to the API itself.
 * - `followLabel` / `unfollowLabel` are ignored — the island renders its own labels, since the
 *   "Following" state changes its label on hover/focus.
 *
 * Nothing is rendered for guests, on one's own profile and where there is nothing to offer
 * (following disabled and not following). The profile header shows it as a button, or, while
 * the friendship system is on, as the follow entry of its controls menu
 * ({@see HeaderControlsMenu}).
 *
 * @author luke
 * @since 0.11
 */
class UserFollowButton extends VueWidget
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string|null ignored since 1.20, see the class docblock
     */
    public $followLabel = null;

    /**
     * @var string|null ignored since 1.20, see the class docblock
     */
    public $unfollowLabel = null;

    /**
     * @var array options of the "Follow" state; only `class` is used since 1.20
     */
    public $followOptions = ['class' => 'btn btn-primary'];

    /**
     * @var array options of the "Following" state; only `class` is used since 1.20
     */
    public $unfollowOptions = ['class' => 'btn btn-outline-primary'];

    /**
     * @var string|null the icon of the "Follow" state, none by default (the "Following" state
     * always shows a check) — e.g. for the entry of the profile header's controls menu
     * ({@see HeaderControlsMenu})
     * @since 1.20
     */
    public ?string $followIcon = null;

    protected string $component = 'UserFollowButton';

    protected ?string $assetBundle = UserVueAsset::class;

    /**
     * @var array|null the follow state, as `user/<id>/follow` answers it
     */
    private ?array $state = null;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (Yii::$app->user->isGuest || $this->user->isCurrentUser()) {
            return false;
        }

        $state = $this->getState();
        if (!$state['canFollow'] && !$state['isFollowing']) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        return [
            'userId' => $this->user->id,
            'userName' => $this->user->getDisplayName(),
            'initial' => $this->getState(),
            'followClass' => $this->followOptions['class'] ?? '',
            'followingClass' => $this->unfollowOptions['class'] ?? '',
            'checkIconHtml' => Icon::get('check')->asString(),
            'followIconHtml' => $this->followIcon ? Icon::get($this->followIcon)->asString() : '',
        ];
    }

    /**
     * `{isFollowing, followerCount, canFollow}` — {@see FollowSerializer::state()}, the shape
     * `user/<id>/follow` answers.
     */
    private function getState(): array
    {
        return $this->state ??= FollowSerializer::state($this->user);
    }
}
