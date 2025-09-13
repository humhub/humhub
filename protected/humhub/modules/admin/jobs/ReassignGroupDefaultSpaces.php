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
use humhub\modules\user\models\GroupSpace;

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
            /* @var GroupSpace[] $groupSpaces */
            $groupSpaces = $group->getAllGroupSpaces()->all();
            foreach ($group->groupUsers as $groupUser) {
                foreach ($groupSpaces as $groupSpace) {
                    $groupSpace->space->addMember($groupUser->user_id);
                }
            }
        }
    }

    public function getExclusiveJobId()
    {
        return 'admin.reassign-default-spaces-for-group-id-' . $this->groupId;
    }
}
