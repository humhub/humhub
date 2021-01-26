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
use yii\base\NotSupportedException;
use humhub\modules\user\models\forms\Login;

/**
 * BaseFormAuth is a base class for AuthClients using the Login Form
 * 
 * @since 1.1
 */
class BaseFormAuth extends BaseClient
{

    /**
     * @var Login the login form model
     */
    public $login = null;

    /**
     * @var User User from submitted login form (by username, without password)
     */
    private $loginUser = null;

    /**
     * Authenticate the user using the login form.
     * 
     * @throws NotSupportedException
     */
    public function auth()
    {
        throw new NotSupportedException('Method "' . get_class($this) . '::' . __FUNCTION__ . '" not implemented.');
    }

    /**
     * Find user by passed username from login form
     *
     * @return User|null
     */
    public function getUserByLogin()
    {
        if (!$this->loginUser) {
            $this->loginUser = ($this->login instanceof Login)
                ? User::find()
                    ->where(['username' => $this->login->username])
                    ->orWhere(['email' => $this->login->username])
                    ->one()
                : null;
        }

        return $this->loginUser;
    }

    /**
     * @return integer
     * @since 1.8
     */
    public function getDelayedLoginTime()
    {
        return $this->getUserByLogin()
            ? $this->getUserByLogin()->getSettings()->get('nextLoginPossibleTime') - time()
            : 0;
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
     */
    public function countFailedLoginAttempts()
    {
        if ($this->getUserByLogin()) {
            $this->getUserByLogin()->getSettings()->set('failedLoginAttemptsCount', $this->getUserByLogin()->getSettings()->get('failedLoginAttemptsCount') + 1);
            $this->delayLoginAfterFailedAttempt();
        }
    }

    /**
     * @since 1.8
     */
    public function resetFailedLoginAttempts()
    {
        if ($this->getUserByLogin()) {
            $this->getUserByLogin()->getSettings()->delete('failedLoginAttemptsCount');
            $this->getUserByLogin()->getSettings()->delete('nextLoginPossibleTime');
        }
    }

    /**
     * @since 1.8
     */
    public function delayLoginAfterFailedAttempt()
    {
        if (!$this->getUserByLogin()) {
            return;
        }

        /* @var $module Module */
        $module = Yii::$app->getModule('user');

        $delaySeconds = $this->getUserByLogin()->getSettings()->get('failedLoginAttemptsCount') <= 5
            ? $module->failedLoginDelayMin
            : $module->failedLoginDelayMax;

        $this->getUserByLogin()->getSettings()->set('nextLoginPossibleTime', time() + $delaySeconds);
    }

    /**
     * @since 1.8
     */
    public function reportAboutFailedLoginAttempts()
    {
        if (!$this->getUserByLogin()) {
            return;
        }

        $failedLoginAttemptsCount = (int)$this->getUserByLogin()->getSettings()->get('failedLoginAttemptsCount');
        if ($failedLoginAttemptsCount > 0) {
            Yii::$app->getView()->warn(Yii::t('UserModule.base', 'Unsuccessful login attempts since last login: {failedLoginAttemptsCount}', [
                '{failedLoginAttemptsCount}' => $failedLoginAttemptsCount
            ]));
        }

        $this->resetFailedLoginAttempts();
    }

}
