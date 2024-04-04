<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\jobs;

use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\models\User;
use yii\base\InvalidArgumentException;

/**
 * Deletes a user
 *
 * @author Luke
 */
class DeleteUser extends LongRunningActiveJob implements ExclusiveJobInterface
{
    public $user_id;

    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        if (empty($this->user_id)) {
            throw new InvalidArgumentException('User id cannot be empty!');
        }

        return 'user.deleteUser.' . $this->user_id;
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        $user = User::findOne(['id' => $this->user_id]);
        if ($user === null) {
            return;
        }

        $user->delete();
    }
}
