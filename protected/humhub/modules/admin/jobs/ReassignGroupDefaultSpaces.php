<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\user\models\Group;

/**
 * Reassign default spaces to all users
 *
 * @since 1.8
 */
class ReassignGroupDefaultSpaces extends ActiveJob implements ExclusiveJobInterface
{
    /**
     * @var int group id
     */
    public $groupId;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $group = Group::findOne(['id' => $this->groupId]);

        if ($group !== null) {
            foreach ($group->groupUsers as $user) {
                foreach ($group->groupSpaces as $space) {
                    if ($space !== null) {
                        $space->space->addMember($user->user_id);
                    }
                }
            }
        }
    }

    public function getExclusiveJobId()
    {
        return 'admin.reassign-default-spaces-for-group-id-' . $this->groupId;
    }
}
