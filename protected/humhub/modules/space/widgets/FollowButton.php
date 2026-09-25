<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\FollowSerializer;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;
use Yii;

/**
 * The follow button of a space: "Follow" ↔ "Following" (reading "Unfollow" on hover/focus).
 *
 * Since 1.20 this widget renders the `FollowButton` Vue island (see its own docblock) with the
 * current follow state inlined; following and unfollowing happen client side against
 * `/api/v2/space/<id>/follow`, and the island keeps itself in sync with the space's
 * `MembershipButton` through domain events rather than markup the server re-renders.
 *
 * ## Properties kept from earlier versions
 *
 * - `followOptions['class']` / `unfollowOptions['class']` become the classes of the "Follow"
 *   and "Following" states. Every other option (`style`, `data-*`, ...) is ignored: the island
 *   renders the button and talks to the API itself.
 * - `followLabel` / `unfollowLabel` are ignored — the island renders its own labels, since the
 *   "Following" state changes its label on hover/focus.
 *
 * Nothing is rendered for guests, for a space the user cannot see or is blocked from, and
 * where there is nothing to offer (following disabled and not following). A member gets the
 * (hidden) island, so following becomes available as soon as the membership ends.
 *
 * @author luke
 * @since 0.11
 */
class FollowButton extends VueWidget
{
    /**
     * @var Space
     */
    public $space;

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
    public $followOptions = ['class' => 'btn btn-secondary btn-sm'];

    /**
     * @var array options of the "Following" state; only `class` is used since 1.20
     */
    public $unfollowOptions = ['class' => 'btn btn-secondary btn-sm active'];

    protected string $component = 'FollowButton';

    protected ?string $assetBundle = SpaceVueAsset::class;

    /**
     * @var array|null the follow state, as `space/<id>/follow` answers it
     */
    private ?array $state = null;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (Yii::$app->user->isGuest
            || $this->space->visibility == Space::VISIBILITY_NONE
            || $this->space->isBlockedForUser()) {
            return false;
        }

        $state = $this->getState();
        if (!$state['canFollow'] && !$state['isFollowing'] && !$this->space->isMember()) {
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
            'spaceId' => $this->space->id,
            'spaceName' => $this->space->getDisplayName(),
            'initial' => $this->getState(),
            'isMember' => $this->space->isMember(),
            'followClass' => $this->followOptions['class'] ?? '',
            'followingClass' => $this->unfollowOptions['class'] ?? '',
            'checkIconHtml' => Icon::get('check')->asString(),
        ];
    }

    /**
     * `{isFollowing, followerCount, canFollow}` — {@see FollowSerializer::state()}, the shape
     * `space/<id>/follow` answers.
     */
    private function getState(): array
    {
        return $this->state ??= FollowSerializer::state($this->space);
    }
}
