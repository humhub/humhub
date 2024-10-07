<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\commands;

use humhub\helpers\DatabaseHelper;
use humhub\libs\DynamicConfig;
use humhub\libs\UUID;
use humhub\modules\installer\libs\InitialData;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use humhub\services\MigrationService;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Console Install
 *
 * Example usage:
 *   php yii installer/write-db-config "$HUMHUB_DB_HOST" "$HUMHUB_DB_NAME" "$HUMHUB_DB_USER" "$HUMHUB_DB_PASSWORD"
 *   php yii installer/install-db
 *   php yii installer/write-site-config "$HUMHUB_NAME" "$HUMHUB_EMAIL"
 *   php yii installer/create-admin-account
 */
class InstallController extends Controller
{
    /**
     * Finished installation without input. Useful for testing.
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
        if (true !== $message = $this->checkDBConnection()) {
            throw new Exception($message ?? "Could not connect to DB!");
        }

        $this->stdout("  * Installing Database\n", Console::FG_YELLOW);

        Yii::$app->cache->flush();

        MigrationService::create()->migrateUp();

        DynamicConfig::rewrite();

        Yii::$app->setDatabaseInstalled();

        $this->stdout("  * Finishing\n", Console::FG_YELLOW);
        Yii::$app->setInstalled();

        return ExitCode::OK;
    }

    /**
     * Creates a new user account and adds it to the admin-group
     */
    public function actionCreateAdminAccount($admin_user = 'admin', $admin_email = 'humhub@example.com', $admin_pass = 'test')
    {
        $user = new User();
        $user->username = $admin_user;
        $user->email = $admin_email;
        $user->status = User::STATUS_ENABLED;
        $user->language = '';
        if (!$user->save()) {
            throw new Exception("Could not save user");
        }

        $user->profile->title = 'System Administration';
        $user->profile->firstname = 'Sys';
        $user->profile->lastname = 'Admin';
        $user->profile->save();

        $password = new Password();
        $password->user_id = $user->id;
        $password->setPassword($admin_pass);
        $password->save();

        Group::getAdminGroup()->addUser($user);

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

        Yii::$app->setInstalled();

        return ExitCode::OK;
    }

    /**
     * Sets the base url
     */
    public function actionSetBaseUrl($base_url)
    {
        $this->stdout("Setting base url", Console::FG_YELLOW);
        Yii::$app->settings->set('baseUrl', $base_url);

        return ExitCode::OK;
    }

    /**
     * Tries to open global db connection and checks result.
     *
     * @return true|null|string
     */
    private function checkDBConnection()
    {
        try {
            // call setActive with true to open connection.
            Yii::$app->db->open();
            // return the current connection state.
            return Yii::$app->db->getIsActive() ?: null;
        } catch (Exception $e) {
            return DatabaseHelper::handleConnectionErrors($e, false, false, true);
        }
    }
}
