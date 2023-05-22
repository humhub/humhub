<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * DatabaseForm holds all required database settings.
 *
 * @since 0.5
 */
class DatabaseForm extends \yii\base\Model
{

    /**
     * @var string hostname
     */
    public $hostname;

    /**
     * @var integer port
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
            'hostname' => Yii::t('InstallerModule.base', 'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)'),
            'port' => Yii::t('InstallerModule.base', 'Optional: Port of your MySQL Database Server. Leave empty to use default port.'),
            'username' => Yii::t('InstallerModule.base', 'Your MySQL username'),
            'password' => Yii::t('InstallerModule.base', 'Your MySQL password.'),
            'database' => Yii::t('InstallerModule.base', 'The name of the database you want to run HumHub in.'),
        ];
    }

}
