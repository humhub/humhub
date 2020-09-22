<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\jobs;

use Yii;
use yii\base\InvalidArgumentException;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\user\models\User;

/**
 * Soft deletes a user
 *
 * @since 1.3
 * @author Luke
 */
class SoftDeleteUser extends ActiveJob implements ExclusiveJobInterface
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

        return 'user.softDeleteUser.' . $this->user_id;
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
        
        $user->softDelete();
    }

}
