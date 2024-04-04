<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\jobs;

use Exception;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use yii\db\StaleObjectException;

class RemoveAllMembersFromSpaceJob extends LongRunningActiveJob
{
    /**
     * @var Space target space
     */
    private $space;

    /**
     * @var int
     */
    public $spaceId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->space = Space::findOne(['id' => $this->spaceId]);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach (Membership::findAll(['space_id' => $this->space->id, 'group_id' => [Space::USERGROUP_MEMBER, Space::USERGROUP_USER, Space::USERGROUP_GUEST]]) as $spaceMembership) {
            try {
                $spaceMembership->delete();
            } catch (StaleObjectException|Exception $e) {
            }
        }
    }
}
