<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\User;

/**
 * Standard password authentication client
 * 
 * @since 1.1
 */
class Password extends BaseFormAuth implements interfaces\PrimaryClient
{

    public $login;
    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return 'local';
    }

    /**
     * @inheritdoc
     */
    protected function defaultName(): string
    {
        return 'password';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle(): string
    {
        return 'Password';
    }

    /**
     * @inheritdoc
     */
    public function auth(): bool
    {
        $user = $this->getUserByLogin();

        if ($user !== null && $user->currentPassword !== null && $user->currentPassword->validatePassword($this->login->password)) {
            $this->setUserAttributes(['id' => $user->id]);
            return true;
        } else {
            $this->countFailedLoginAttempts();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        $attributes = $this->getUserAttributes();
        return User::findOne(['id' => $attributes['id'], 'auth_mode' => $this->getId()]);
    }

}
