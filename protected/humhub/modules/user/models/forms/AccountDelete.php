<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Auth;
use humhub\modules\user\jobs\SoftDeleteUser;
use humhub\components\db\AfterCommit;

/**
 * AccountDelete is the model for account deletion.
 *
 * @since 0.5
 */
class AccountDelete extends Model
{
    /**
     * @since 1.3
     * @var User the user
     */
    public $user;

    /**
     * Perform user deletion. The user confirmed through the sudo mode (`SudoFilter` on the delete action), so the
     * form has no current password field.
     * @since 1.3
     */
    public function performDelete()
    {
        $this->user->status = User::STATUS_SOFT_DELETED;
        $this->user->save();

        $userId = $this->user->id;
        AfterCommit::run(static fn() => Yii::$app->queue->push(new SoftDeleteUser(['user_id' => $userId])));

        return true;
    }
}
