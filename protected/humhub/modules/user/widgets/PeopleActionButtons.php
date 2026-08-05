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
            'variant' => FriendshipButton::VARIANT_DIRECTORY,
        ]);
    }

}
