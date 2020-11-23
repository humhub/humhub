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
use Yii;
use yii\queue\RetryableJobInterface;

/**
 * Reassign default spaces to all users
 *
 * @since 1.8
 */
class ReassignAllDefaultSpaces extends ActiveJob implements ExclusiveJobInterface, RetryableJobInterface
{
    /**
     * @var int group id
     */
    public $groupId;

    /**
     * @var int maximum 2 hours
     */
    private $maxExecutionTime = 60 * 60 * 2;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $group = Group::findOne(['id' => $this->groupId]);

        foreach ($group->groupUsers as $user) {
            foreach ($group->groupSpaces as $group_space) {
                $group_space->space->addMember($user->user_id);
            }
        }
    }

    public function getExclusiveJobId()
    {
        return 'admin.reassign-default-spaces-for-group-id-' . $this->groupId;
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return $this->maxExecutionTime;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }
}
