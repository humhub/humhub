<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\models\filters;

use humhub\modules\user\models\User;

class BlockedUsersStreamFilter extends StreamQueryFilter
{
    /**
     * @var array IDs of the blocked users for the current User
     */
    private $blockedUsers;

    public function init() {
        parent::init();

        if (!empty($this->streamQuery->user) && $this->streamQuery->user instanceof User) {
            $this->blockedUsers = $this->streamQuery->user->getBlockedUserIds();
        }
    }

    public function apply()
    {
        if (empty($this->blockedUsers)) {
            return;
        }

        $this->query->andWhere(['NOT IN', 'user.id', $this->blockedUsers]);
    }
}
