<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\jobs;

use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\UserAddedNotification;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;

class AddUsersToSpaceJob extends LongRunningActiveJob
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
     * @var int[]
     */
    public $userIds;

    /**
     * @var User originator user
     */
    private $originator;

    /**
     * @var User originator user id
     */
    public $originatorId;

    /**
     * @var bool
     */
    public $allUsers = false;

    /**
     * @var bool
     */
    public $forceMembership = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->space = Space::findOne(['id' => $this->spaceId]);
        $this->originator = User::findOne(['id' => $this->originatorId]);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $addedUserIds = [];
        if ($this->allUsers) {
            foreach (User::find()->active()->batch() as $users) {
                $addedUserIds = array_merge($addedUserIds, $this->addUsers($users));
            }
        } else {
            $addedUserIds = $this->addUsers($this->userIds);
        }

        // one notification dispatch for all users added directly
        if ($addedUserIds !== []) {
            UserAddedNotification::send($addedUserIds, $this->space, $this->originator, dedupe: false);
        }
    }

    /**
     * @param User[]|int[] $users
     * @return int[] the ids of the users added as members directly (`forceMembership`)
     */
    private function addUsers($users): array
    {
        $addedUserIds = [];
        foreach ($users as $user) {
            try {
                $user = ($user instanceof User) ? $user : User::findOne(['id' => $user]);

                if (!$user || $user->id === $this->originator->id || $this->space->isMember($user->id)) {
                    continue;
                }

                $this->space->inviteMember($user->id, $this->originator->id, !$this->forceMembership);

                if ($this->forceMembership) {
                    if ($this->space->addMember($user->id, 2, true)) {
                        $addedUserIds[] = (int)$user->id;
                    }
                }
            } catch (Exception $e) {
                Yii::error($e);
            }
        }

        return $addedUserIds;
    }
}
