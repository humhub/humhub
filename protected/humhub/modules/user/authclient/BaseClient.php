<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

/**
 * Extended BaseClient with additional events
 *
 * @since 1.1
 * @author luke
 */
class BaseClient extends \yii\authclient\BaseClient
{

    /**
     * @event Event an event raised on update user data.
     * @see AuthClientHelpers::updateUser()
     */
    const EVENT_UPDATE_USER = 'update';

    /**
     * @event Event an event raised on create user.
     * @see AuthClientHelpers::createUser()
     */
    const EVENT_CREATE_USER = 'create';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        
    }

    /**
     * Find user based on the form attributes
     *
     * @return User|null
     */
    public function getUser()
    {
        return null;
    }

    /**
     * @return integer
     * @since 1.8
     */
    public function getDelayedLoginTime()
    {
        if (!$this->getUser()) {
            return 0;
        }

        return $this->getUser()->getSettings()->get('nextLoginPossibleTime') - time();
    }

    /**
     * @since 1.8
     * @return boolean
     */
    public function isDelayedLoginAction()
    {
        return $this->getDelayedLoginTime() > 0;
    }

    /**
     * @since 1.8
     * @param User
     */
    public function countFailedLoginAttempts($user = null)
    {
        if ($user) {
            $user->getSettings()->set('failedLoginAttemptsCount', $user->getSettings()->get('failedLoginAttemptsCount') + 1);
            $this->delayLoginAfterFailedAttempt($user);
        }
    }

    /**
     * @since 1.8
     * @param User
     */
    public function resetFailedLoginAttempts($user)
    {
        if ($user) {
            $user->getSettings()->delete('failedLoginAttemptsCount');
            $user->getSettings()->delete('nextLoginPossibleTime');
        }
    }

    /**
     * @since 1.8
     * @param User
     */
    protected function delayLoginAfterFailedAttempt($user)
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');

        $delaySeconds = $user->getSettings()->get('failedLoginAttemptsCount') <= 5
            ? $module->failedLoginDelayMin
            : $module->failedLoginDelayMax;

        $user->getSettings()->set('nextLoginPossibleTime', time() + $delaySeconds);
    }

    /**
     * @since 1.8
     * @param User
     */
    public function reportAboutFailedLoginAttempts($user)
    {
        if (!$user) {
            return;
        }

        $failedLoginAttemptsCount = (int)$user->getSettings()->get('failedLoginAttemptsCount');
        if ($failedLoginAttemptsCount > 0) {
            Yii::$app->getView()->warn(Yii::t('UserModule.base', 'Unsuccessful login attempts since last login: {failedLoginAttemptsCount}', [
                '{failedLoginAttemptsCount}' => $failedLoginAttemptsCount
            ]));
        }

        $this->resetFailedLoginAttempts($user);
    }

}
