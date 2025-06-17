<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use humhub\modules\installer\Module;
use Yii;
use yii\base\Model;

/**
 * DatabaseForm holds all required database settings.
 *
 * @since 0.5
 */
class DatabaseForm extends Model
{
    /**
     * @var string hostname
     */
    public $hostname;

    /**
     * @var int port
     */
    public $port;

    /**
     * @var string username
     */
    public $username;

    /**
     * @var string password
     */
    public $password;

    /**
     * @var string database name
     */
    public $database;

    /**
     * @var string Create database if it doesn't exist
     */
    public $create;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hostname', 'username', 'database'], 'required'],
            ['password', 'safe'],
            ['port', 'integer'],
            ['create', 'in', 'range' => [0, 1]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'hostname' => Yii::t('InstallerModule.base', 'Hostname'),
            'port' => Yii::t('InstallerModule.base', 'Port'),
            'username' => Yii::t('InstallerModule.base', 'Username'),
            'password' => Yii::t('InstallerModule.base', 'Password'),
            'database' => Yii::t('InstallerModule.base', 'Name of Database'),
            'create' => Yii::t('InstallerModule.base', 'Create the database if it doesn\'t exist yet.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'hostname' => Yii::t(
                'InstallerModule.base',
                'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)',
            ),
            'port' => Yii::t(
                'InstallerModule.base',
                'Optional: Port of your MySQL Database Server. Leave empty to use default port.',
            ),
            'username' => Yii::t('InstallerModule.base', 'Your MySQL username'),
            'password' => Yii::t('InstallerModule.base', 'Your MySQL password.'),
            'database' => Yii::t('InstallerModule.base', 'The name of the database you want to run HumHub in.'),
        ];
    }

    public function autoLoad(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('installer');

        if (!$module->enableAutoSetup) {
            return false;
        }

        $this->username = Yii::$app->db->username;
        $this->password = Yii::$app->db->password;
        $this->create = 1;

        $connectionString = Yii::$app->db->dsn;

        if (preg_match('/host=([^;]+)/', $connectionString ?: '', $matches)) {
            $this->hostname = $matches[1];
        }
        if (preg_match('/port=([^;]+)/', $connectionString ?: '', $matches)) {
            $this->port = $matches[1];
        }
        if (preg_match('/dbname=([^;]+)/', $connectionString ?: '', $matches)) {
            $this->database = $matches[1];
        }

        return true;
    }

    private function getDsn(bool $includeDatabaseName = true): string
    {
        $connectionString = 'mysql:host=' . $this->hostname;
        if ($this->port !== '') {
            $connectionString .= ';port=' . $this->port;
        }
        if ($includeDatabaseName) {
            $connectionString .= ';dbname=' . $this->database;
        }

        return $connectionString;
    }

    public function getDbConfigAsArray(bool $includeDatabaseName = true): array
    {
        return [
            'class' => 'yii\db\Connection',
            'dsn' => $this->getDsn($includeDatabaseName),
            'username' => $this->username,
            'password' => $this->password,
            'charset' => 'utf8',
        ];
    }

}
