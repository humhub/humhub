<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\commands;

use humhub\modules\admin\models\forms\UserDeleteForm;
use humhub\modules\space\helpers\MembershipHelper;
use yii\console\Controller;
use yii\console\ExitCode;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;
use yii\console\widgets\Table;
use yii\helpers\Console;

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
     * @var bool True - Hard Delete, error if Space Owner
     */
    public $full;

    /**
     * @var bool True - Hard Delete including owned Spaces
     */
    public $force;

    public function options($actionID)
    {
        if (in_array($actionID, ['delete', 'delete-disabled'])) {
            return ['full', 'force'];
        }

        return [];
    }

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
        $user = $this->getUser($username);
        if (!$user) {
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
        $user = $this->getUser($username);
        if (!$user) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Group::getAdminGroup()->addUser($user);

        $this->stdout("User with ID " . $user->id . " successfully added to the administrator group!\n\n");
        return ExitCode::OK;
    }

    /**
     * Delete a user account.
     */
    public function actionDelete(string $username)
    {
        $user = $this->getUser($username);
        if (!$user) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $model = new UserDeleteForm(['user' => $user]);

        if ($this->full) {
            // Delete all contributions of the user
            $model->deleteContributions = true;

            if ($this->force) {
                // Delete all spaces which are owned by the user
                $model->deleteSpaces = true;
            } elseif (count(MembershipHelper::getOwnSpaces($user)) !== 0) {
                $this->stderr(
                    "Could not delete user which is owner of Spaces! (Use --force option to delete its space as well.)\n\n",
                );
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        if (!$model->performDelete()) {
            $this->stderr("Could not delete user!\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("User with ID " . $user->id . " deletion process queued!\n\n");
        return ExitCode::OK;
    }

    public function actionDeleteDisabled(array $exceptUserIds = [])
    {
        $query = User::find()->where(['status' => User::STATUS_DISABLED])->andWhere(['not in', 'id', $exceptUserIds]);
        $users = array_map(function ($user) {
            return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email];
        }, $query->all());

        if (count($users) === 0) {
            $this->stdout(
                $this->ansiFormat("\nThere are no disabled users to process!\n\n", Console::FG_GREEN),
            );
            return ExitCode::OK;
        }

        echo Table::widget(['headers' => ['ID', 'Username', 'E-Mail'], 'rows' => $users]);

        $confirmMessage = (!$this->full)
            ? 'Are you sure you want to perform a (soft) delete? This permanently removes the user profiles.'
            : 'Are you sure you want to perform a (hard) delete? This permanently removes users AND all their content/spaces.';


        if ($this->confirm($confirmMessage)) {
            $count = 0;
            foreach ($query->all() as $user) {
                $model = new UserDeleteForm(['user' => $user]);
                $model->deleteContributions = ($this->full);
                if ($this->force) {
                    $model->deleteSpaces = true;
                } elseif (count(MembershipHelper::getOwnSpaces($user)) !== 0) {
                    $this->stderr(
                        $user->displayName . ": Skipped. User is still an owner of one or more Spaces. (To delete these Spaces as well, use the --force flag.)\n\n",
                    );
                    continue;
                }
                if ($model->performDelete()) {
                    $count++;
                }
            }

            $this->stdout(
                $this->ansiFormat(
                    sprintf(
                        "\nSuccessfully queued %d users for deletion. The process may take some time to complete.\n\n",
                        $count,
                    ),
                    Console::FG_GREEN,
                ),
            );
        }
        return ExitCode::OK;
    }


    public function actionRemoveSoftDeleted(array $exceptUserIds = [])
    {
        $query = User::find()->where(['status' => User::STATUS_SOFT_DELETED])
            ->andWhere(['not in', 'id', $exceptUserIds]);

        $users = array_map(function ($user) {
            return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email];
        }, $query->all());

        if (count($users) === 0) {
            $this->stdout(
                $this->ansiFormat("\nThere are no soft-deleted users to process!\n\n", Console::FG_GREEN),
            );
            return ExitCode::OK;
        }

        echo Table::widget(['headers' => ['ID', 'Username', 'E-Mail'], 'rows' => $users]);

        if ($this->confirm(
            'Are you sure you want to fully delete these users? This includes all user-created content.',
        )) {
            $count = 0;
            foreach ($query->all() as $user) {
                $model = new UserDeleteForm(['user' => $user]);
                $model->deleteContributions = true;
                if ($model->performDelete()) {
                    $count++;
                }
            }

            $this->stdout(
                $this->ansiFormat(
                    sprintf("\nSuccessfully deleted %d users and all their data.\n\n", $count),
                    Console::FG_GREEN,
                ),
            );
        }
        return ExitCode::OK;
    }


    private function getUser(string $username): false|User
    {
        /** @var User $user */
        $user = User::find()->where(['username' => $username])->one();
        if ($user === null) {
            $this->stderr("Could not find user!\n\n");
            return false;
        }

        return $user;
    }
}
