<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\UserAddedNotification;
use humhub\modules\user\models\User;

class AddUsersToSpaceJob extends ActiveJob
{
    /** @var Space */
    public $space;

    /** @var User[] */
    public $users;

    /** @var bool */
    public $allUsers = false;

    /** @var User */
    public $originator;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->allUsers) {
            foreach (User::find()->where(['status' => User::STATUS_ENABLED])->batch() as $users) {
                $this->addUsers($users);
            };
        } else {
            $this->addUsers($this->users);
        }
    }

    /**
     * @param User[] $users
     * @throws \yii\base\Exception
     */
    private function addUsers($users)
    {
        foreach ($users as $user) {
            if ($user->id === $this->originator->id) {
                continue;
            }
            $this->space->inviteMember($user->id, $this->originator->id, false);
            if ($this->space->addMember($user->id, 2, true) === false) {
                \Yii::error(
                    'The User ' . $user->getDisplayName() . ' can not be added to Space ' . $this->space->getDisplayName(),
                    'Space.Jobs.AddUsersToSpace'
                );
            };
            UserAddedNotification::instance()->from($this->originator)->about($this->space)->send($user);
        }
    }
}
