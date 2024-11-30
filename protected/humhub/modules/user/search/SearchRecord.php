<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\interfaces\MetaSearchResultInterface;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;

/**
 * Search Record for User
 *
 * @author luke
 * @since 1.16
 */
class SearchRecord implements MetaSearchResultInterface
{
    public ?User $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): string
    {
        return Image::widget([
            'user' => $this->user,
            'width' => 36,
            'link' => false,
            'hideOnlineStatus' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->user->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        $profile = $this->user->profile;

        return $profile instanceof Profile && isset($profile->title) ? $profile->title : '';
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->user->getUrl();
    }
}
