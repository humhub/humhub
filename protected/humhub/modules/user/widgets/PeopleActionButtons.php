<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\components\Widget;
use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\models\User;

/**
 * PeopleActionsButton shows directory options (following or friendship) for listed users
 *
 * @since 1.9
 * @author Luke
 * @deprecated since 1.20, will be removed in 1.21 — use UserList / the PeopleDirectory island
 *   (`humhub\modules\user\widgets\PeopleDirectory`). Unused by the core since 1.20; kept for
 *   modules building their own People-like page on it.
 *   The card's buttons are `FriendshipButton` / `UserFollowButton` islands and the extension
 *   slot `user.card-actions` of `PeopleCard.vue` now.
 */
class PeopleActionButtons extends Widget
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string Template for buttons
     */
    public $template = '{buttons}';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = $this->addFollowButton();
        $html .= $this->addFriendshipButton();

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }


    protected function addFollowButton(): string
    {
        return UserFollowButton::widget([
            'user' => $this->user,
            'followOptions' => ['class' => 'btn btn-primary btn-sm'],
            'unfollowOptions' => ['class' => 'btn btn-outline-primary btn-sm'],
        ]);
    }

    protected function addFriendshipButton(): string
    {
        return FriendshipButton::widget([
            'user' => $this->user,
            'buttonClass' => 'btn btn-accent btn-sm',
            'stateClass' => 'btn btn-sm btn-outline-accent',
            'togglerClass' => 'btn btn-sm btn-outline-accent',
        ]);
    }

}
