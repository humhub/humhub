<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\base\Exception;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;

/**
 * Console User management
 * 
 * Example usage:
 *   php yii user/create-admin-account
 *   php yii user/create-account "$HUMHUB_USERNAME" "$HUMHUB_EMAIL" "$HUMHUB_PASSWORD"
 *   php yii user/set-user-password "$HUMHUB_USERNAME" "$HUMHUB_PASSWORD"
 * 
 * @author Luke
 * @author Michael Riedmann
 * @author Mathieu Brunot
 */
class UserController extends Controller
{

    /**
     * Creates a new user account and adds it to the admin-group
     */
    public function actionCreateAdminAccount($admin_user, $admin_email, $admin_pass,
        $admin_title, $admin_firstname, $admin_lastname)
    {
        $user = $this->createUser($admin_user, $admin_email, $admin_pass, $admin_title, $admin_firstname, $admin_lastname);
        $this->addUserToAdminGroup($user);

        return ExitCode::OK;
    }

    /**
     * Creates a new user account.
     */
    public function actionCreateUser(string $username, string $email, string $pass, string $title, string $firstname, string $lastname)
    {
        $user = $this->createUser($admin_user, $admin_email, $admin_pass, $admin_title, $admin_firstname, $admin_lastname);

        return !empty( $user ) ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Creates a new user account.
     */
    private function createUser(string $username, string $email, string $pass, string $title, string $firstname, string $lastname): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->status = User::STATUS_ENABLED;
        $user->language = '';
        if (!$user->save()) {
            throw new Exception("Could not save user");
        }

        $user->profile->title = $title;
        $user->profile->firstname = $firstname;
        $user->profile->lastname = $lastname;
        $user->profile->save();
        $this->stdout("User created\n", Console::FG_YELLOW);

        $this->setUserPassword($user, $pass);

        return $user;
    }

    /**
     * Sets the password for a user account
     */
    public function actionSetUserPassword(string $username, string $pass)
    {
        $searchModel = new UserSearch();
        $searchModel->username = $username;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $users = $dataProvider->getModels();
        if ( !empty( $users ) && 1 === count( $users ) ) {
            $this->setUserPassword($users[0], $pass);
            return ExitCode::OK;
        }
        throw new Exception("Could not find user");
    }

    /**
     * Sets the password for a user account.
     */
    private function setUserPassword(User $user, string $pass)
    {
        $password = new Password();
        $password->user_id = $user->id;
        $password->setPassword($pass);
        $password->save();
        $this->stdout("User password reset\n", Console::FG_YELLOW);
    }

    /**
     * Make a user account admin.
     */
    public function actionMakeAdmin(string $username)
    {
        $searchModel = new UserSearch();
        $searchModel->username = $username;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $users = $dataProvider->getModels();
        if ( !empty( $users ) && 1 === count( $users ) ) {
            $this->addUserToAdminGroup($users[0]);
            return ExitCode::OK;
        }
        throw new Exception("Could not find user");
    }

    /**
     * Adds a user account to the admin-group.
     */
    private function addUserToAdminGroup(User $user)
    {
        Group::getAdminGroup()->addUser($user);
        $this->stdout("User added to admin group\n", Console::FG_YELLOW);
    }
}
