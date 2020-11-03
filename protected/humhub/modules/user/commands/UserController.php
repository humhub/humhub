<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;

/**
 * Console User Management
 *
 * Example usage:
 *   php yii user/create "john.doe" "jd@example.com" "John" "Doe"
 *   php yii user/set-password "john.doe" "secret"
 *   php yii user/make-admin "john.doe"
 *
 * @since 1.7
 * @author Luke
 * @author Michael Riedmann
 * @author Mathieu Brunot
 */
class UserController extends Controller
{

    /**
     * Creates a new user account.
     */
    public function actionCreate(string $userName, string $email, string $firstName, string $lastName)
    {
        $user = new User();
        $user->scenario = User::SCENARIO_EDIT_ADMIN;
        $user->load(['username' => $userName, 'email' => $email], '');
        $user->validate();

        $profile = new Profile();
        $profile->scenario = Profile::SCENARIO_EDIT_ADMIN;
        $profile->load(['firstname' => $firstName, 'lastname' => $lastName], '');
        $profile->validate();

        if ($user->hasErrors() || $profile->hasErrors()) {
            $this->stderr("Could not create user!\n\n");
            $this->stderr("Validation errors:\n");
            print_r($user->getErrors());
            print_r($profile->getErrors());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($user->save()) {
            $profile->user_id = $user->id;
            if ($profile->save()) {
                $this->stdout('User with ID ' . $user->id . ' successfully created!');
                return ExitCode::OK;
            }
        }

        $this->stderr("Could not create user!\n\n");
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Sets the password for an user account.
     */
    public function actionSetPassword(string $username, string $password)
    {
        /** @var User $user */
        $user = User::find()->where(['username' => $username])->one();
        if ($user === null) {
            $this->stderr("Could not find user!\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $passwordModel = new Password();
        $passwordModel->user_id = $user->id;
        $passwordModel->setPassword($password);
        $passwordModel->save();

        $this->stdout("Password for user with ID " . $user->id . " successfully created!\n\n");
        return ExitCode::OK;
    }

    /**
     * Add user to the admin group.
     */
    public function actionMakeAdmin(string $username)
    {
        /** @var User $user */
        $user = User::find()->where(['username' => $username])->one();
        if ($user === null) {
            $this->stderr("Could not find user!\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Group::getAdminGroup()->addUser($user);

        $this->stdout("User with ID " . $user->id . " successfully added to the administrator group!\n\n");
        return ExitCode::OK;
    }
}

