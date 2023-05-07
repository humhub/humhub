<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\base\Exception;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;
use humhub\modules\installer\libs\InitialData;
use humhub\libs\UUID;
use humhub\libs\DynamicConfig;

/**
 * Console Install
 *
 * Example usage:
 *   php yii installer/write-db-config "$HUMHUB_DB_HOST" "$HUMHUB_DB_NAME" "$HUMHUB_DB_USER" "$HUMHUB_DB_PASSWORD"
 *   php yii installer/install-db
 *   php yii installer/write-site-config "$HUMHUB_NAME" "$HUMHUB_EMAIL"
 *   php yii installer/create-admin-account
 *
 * @author Luke
 * @author Michael Riedmann
 * @author Mathieu Brunot
 */
class InstallController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'status';

    /**
     * Finished install without input. Useful for testing.
     */
    public function actionAuto()
    {
        $this->actionWriteSiteConfig();
        $this->actionCreateAdminAccount();

        return ExitCode::OK;
    }

    /**
     * Tries to open a connection to given db.
     * On success: Writes given settings to config-file and reloads it.
     * On failure: Throws exception
     */
    public function actionWriteDbConfig($db_host, $db_name, $db_user, $db_pass)
    {
        $connectionString = "mysql:host=" . $db_host . ";dbname=" . $db_name;
        $dbConfig = [
            'class' => 'yii\db\Connection',
            'dsn' => $connectionString,
            'username' => $db_user,
            'password' => $db_pass,
            'charset' => 'utf8',
        ];

        $temporaryConnection = Yii::createObject($dbConfig);
        $temporaryConnection->open();

        $config = DynamicConfig::load();

        $config['components']['db'] = $dbConfig;
        $config['params']['installer']['db']['installer_hostname'] = $db_host;
        $config['params']['installer']['db']['installer_database'] = $db_name;

        DynamicConfig::save($config);

        return ExitCode::OK;
    }

    /**
     * Checks configured db, flushes caches, runs migrations and sets installed state in config
     */
    public function actionInstallDb()
    {
        $this->stdout("Install DB:\n\n", Console::FG_YELLOW);

        $this->stdout("  * Checking Database Connection\n", Console::FG_YELLOW);
        if (!$this->checkDBConnection()) {
            throw new Exception("Could not connect to DB!");
        }

        $this->stdout("  * Installing Database\n", Console::FG_YELLOW);

        Yii::$app->cache->flush();
        // Disable max execution time to avoid timeouts during migrations
        @ini_set('max_execution_time', 0);
        \humhub\commands\MigrateController::webMigrateAll();

        DynamicConfig::rewrite();

        $this->stdout("  * Finishing\n", Console::FG_YELLOW);
        $this->setDatabaseInstalled();

        return ExitCode::OK;
    }

    /**
     * Creates a new user account and adds it to the admin-group
     */
    public function actionCreateAdminAccount(
        $admin_user = 'admin',
        $admin_email = 'humhub@example.com',
        $admin_pass = 'test',
        $admin_title = 'System Administration',
        $admin_firstname = 'Sys',
        $admin_lastname = 'Admin'
    ) {
        $user = $this->createUser(
            $admin_user,
            $admin_email,
            $admin_pass,
            $admin_title,
            $admin_firstname,
            $admin_lastname
        );
        $this->addUserToAdminGroup($user);

        return ExitCode::OK;
    }

    /**
     * Writes essential site settings to config file and sets installed state
     */
    public function actionWriteSiteConfig($site_name = 'HumHub', $site_email = 'humhub@example.com')
    {
        $this->stdout("Install Site:\n\n", Console::FG_YELLOW);
        InitialData::bootstrap();

        Yii::$app->settings->set('name', $site_name);
        Yii::$app->settings->set('mailer.systemEmailName', $site_email);
        Yii::$app->settings->set('secret', UUID::v4());
        Yii::$app->settings->set('timeZone', Yii::$app->timeZone);

        $this->setInstalled();

        return ExitCode::OK;
    }

    public function actionSetBaseUrl($base_url)
    {
        $this->stdout("Setting base url", Console::FG_YELLOW);
        Yii::$app->settings->set('baseUrl', $base_url);

        return ExitCode::OK;
    }

    /**
     * Checks install status
     */
    public function actionStatus()
    {
        $config = DynamicConfig::load();

        if (!isset($config['params']['databaseInstalled']) || empty($config['params']['databaseInstalled'])) {
            $this->stdout("HumHub database is not installed\n", Console::FG_YELLOW);
        } elseif (!isset($config['params']['installed']) || empty($config['params']['installed'])) {
            $this->stdout("HumHub is not installed\n", Console::FG_YELLOW);
        } else {
            $this->stdout("HumHub is installed\n");
        }

        return ExitCode::OK;
    }

    /**
     * Sets application in installed state (disables installer)
     */
    private function setInstalled()
    {
        $config = DynamicConfig::load();
        $config['params']['installed'] = true;
        DynamicConfig::save($config);
    }

    /**
     * Sets application database in installed state
     */
    private function setDatabaseInstalled()
    {
        $config = DynamicConfig::load();
        $config['params']['databaseInstalled'] = true;
        DynamicConfig::save($config);
    }

    /**
     * Tries to open global db connection and checks result.
     */
    private function checkDBConnection()
    {
        try {
            // call setActive with true to open connection.
            Yii::$app->db->open();
            // return the current connection state.
            return Yii::$app->db->getIsActive();
        } catch (Exception $e) {
            $this->stderr($e->getMessage());
        }
        return false;
    }

    /**
     * Creates a new user account.
     */
    private function createUser(
        string $username,
        string $email,
        string $pass,
        string $title,
        string $firstname,
        string $lastname
    ): User {
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
    private function setUserPassword(User $user, string $pass)
    {
        $password = new Password();
        $password->user_id = $user->id;
        $password->setPassword($pass);
        $password->save();
        $this->stdout("User password reset\n", Console::FG_YELLOW);
    }

    /**
     * Adds a user account to the admin-group
     */
    private function addUserToAdminGroup(User $user)
    {
        Group::getAdminGroup()->addUser($user);
        $this->stdout("User added to admin group\n", Console::FG_YELLOW);
    }
}
