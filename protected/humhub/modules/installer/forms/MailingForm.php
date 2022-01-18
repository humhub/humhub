<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use Yii;

/**
 * MailingForm holds all required SMTP settings.
 */
class MailingForm extends \yii\base\Model
{

    /**
     * @var string transport type (php, smtp or file)
     */
    public $transportType;

    /**
     * @var string email sender address
     */
    public $systemEmailAddress;

    /**
     * @var string email sender name
     */
    public $systemEmailName;

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
     * @var string encryption
     */
    public $encryption;

    /**
     * @var integer
     */
    public $allowSelfSignedCerts;

    /**
     * @var boolean
     */
    public $sendTest;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transportType', 'systemEmailAddress', 'systemEmailName'], 'required'],
            [['hostname', 'username', 'password', 'encryption'], 'required', 'when' => function ($model) {
                return $model->transportType == 'smtp';
            }],
            ['port', 'integer'],
            ['transportType', 'in', 'range' => array_keys(self::getTransportTypeOtions())],
            ['encryption', 'in', 'range' => array_keys(self::getEncryptionOptions())],
            [['allowSelfSignedCerts', 'sendTest'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'systemEmailAddress' => Yii::t('InstallerModule.settings', 'E-Mail sender address'),
            'systemEmailName' => Yii::t('InstallerModule.settings', 'E-Mail sender name'),
            'transportType' => Yii::t('InstallerModule.settings', 'Mail Transport Type'),
            'hostname' => Yii::t('InstallerModule.base', 'Hostname'),
            'port' => Yii::t('InstallerModule.base', 'Port'),
            'username' => Yii::t('InstallerModule.base', 'Username'),
            'password' => Yii::t('InstallerModule.base', 'Password'),
            'encryption' => Yii::t('InstallerModule.base', 'Encryption'),
            'allowSelfSignedCerts' => Yii::t('InstallerModule.base', 'Allow Self-Signed Certificates'),
            'sendTest' => Yii::t('InstallerModule.base', 'Send test e-mail to check configuration.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'systemEmailAddress' => Yii::t('InstallerModule.settings', 'E-Mail address from which e-mails will be sent.'),
            'systemEmailName' => Yii::t('InstallerModule.settings', 'E-Mail sender name'),
            'transportType' => Yii::t('InstallerModule.settings', 'Mail Transport Type'),
            'hostname' => Yii::t('InstallerModule.base', 'Hostname of your SMTP Server'),
            'port' => Yii::t('InstallerModule.base', 'Optional: Port of your SMTP Server. Leave empty to use default port.'),
            'username' => Yii::t('InstallerModule.base', 'Your SMTP username'),
            'password' => Yii::t('InstallerModule.base', 'Your SMTP password.'),
            'encryption' => Yii::t('InstallerModule.base', 'Your SMTP encryption (SSL, TLS, etc.).'),
            'allowSelfSignedCerts' => Yii::t('InstallerModule.base', 'Allow Self-Signed Certificates'),
        ];
    }

    public static function getEncryptionOptions()
    {
        return [
            '' => 'None',
            'ssl' => 'SSL',
            'tls' => 'TLS'
        ];
    }

    public static function getTransportTypeOtions()
    {
        return [
            'file' => 'File (Use for testing/development)',
            'php' => 'PHP',
            'smtp' => 'SMTP'
        ];
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $settingsManager->set('mailer.transportType', $this->transportType);
        $settingsManager->set('mailer.hostname', $this->hostname);
        $settingsManager->set('mailer.username', $this->username);
        if ($this->password != '---invisible---')
            $settingsManager->set('mailer.password', $this->password);
        $settingsManager->set('mailer.port', $this->port);
        $settingsManager->set('mailer.encryption', $this->encryption);
        $settingsManager->set('mailer.allowSelfSignedCerts', $this->allowSelfSignedCerts);
        $settingsManager->set('mailer.systemEmailAddress', $this->systemEmailAddress);
        $settingsManager->set('mailer.systemEmailName', $this->systemEmailName);

        \humhub\libs\DynamicConfig::rewrite();

        return true;
    }
}
