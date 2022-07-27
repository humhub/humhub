<?php

namespace humhub\modules\admin\models\forms;

use humhub\libs\DynamicConfig;
use Yii;
use yii\base\Model;

/**
 * MailingSettingsForm
 *
 * @since 0.5
 */
class MailingSettingsForm extends Model
{
    const TRANSPORT_SMTP = 'smtp';
    const TRANSPORT_FILE = 'file';
    const TRANSPORT_DSN = 'dsn';
    const TRANSPORT_PHP = 'php';
    const TRANSPORT_CONFIG = 'config';

    public $systemEmailAddress;
    public $systemEmailName;
    public $systemEmailReplyTo;
    public $transportType;

    public $dsn;
    public $hostname;
    public $username;
    public $password;
    public $port;
    public $useSmtps;
    public $allowSelfSignedCerts;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->transportType = $settingsManager->get('mailer.transportType');
        $this->dsn = $settingsManager->get('mailer.dsn');
        $this->hostname = $settingsManager->get('mailer.hostname');
        $this->username = $settingsManager->get('mailer.username');
        if ($settingsManager->get('mailer.password') != '')
            $this->password = '---invisible---';

        $this->useSmtps = $settingsManager->get('mailer.useSmtps');
        $this->port = $settingsManager->get('mailer.port');
        $this->allowSelfSignedCerts = $settingsManager->get('mailer.allowSelfSignedCerts');
        $this->systemEmailAddress = $settingsManager->get('mailer.systemEmailAddress');
        $this->systemEmailName = $settingsManager->get('mailer.systemEmailName');
        $this->systemEmailReplyTo = $settingsManager->get('mailer.systemEmailReplyTo');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transportType', 'systemEmailAddress', 'systemEmailName'], 'required'],
            ['transportType', 'in', 'range' => (array_keys($this->getTransportTypes()))],
            [['allowSelfSignedCerts', 'useSmtps'], 'boolean'],
            ['systemEmailAddress', 'email'],
            ['port', 'integer', 'min' => 1, 'max' => 65535],
            [['hostname', 'port'], 'required', 'when' => function ($model) {
                return $model->transportType === self::TRANSPORT_SMTP;
            }],
            ['dsn', 'required', 'when' => function ($model) {
                return $model->transportType === self::TRANSPORT_DSN;
            }],
            [['transportType', 'hostname', 'username', 'password', 'useSmtps', 'allowSelfSignedCerts', 'systemEmailAddress', 'systemEmailName', 'systemEmailReplyTo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dsn' => Yii::t('AdminModule.settings', 'DSN'),
            'systemEmailAddress' => Yii::t('AdminModule.settings', 'E-Mail sender address'),
            'systemEmailName' => Yii::t('AdminModule.settings', 'E-Mail sender name'),
            'systemEmailReplyTo' => Yii::t('AdminModule.settings', 'E-Mail reply-to'),
            'transportType' => Yii::t('AdminModule.settings', 'Mail Transport Type'),
            'username' => Yii::t('AdminModule.settings', 'Username'),
            'password' => Yii::t('AdminModule.settings', 'Password'),
            'port' => Yii::t('AdminModule.settings', 'Port number'),
            'useSmtps' => Yii::t('AdminModule.settings', 'Use SMTPS'),
            'allowSelfSignedCerts' => Yii::t('AdminModule.settings', 'Allow Self-Signed Certificates?'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'systemEmailReplyTo' => Yii::t('AdminModule.settings', 'Optional. Default reply address for system emails like notifications.'),
            'dsn' => Yii::t('AdminModule.settings', 'e.g. smtps://user:pass@smtp.example.com:port'),
            'port' => Yii::t('AdminModule.settings', 'e.g. 25 (for SMTP) or 587 (for SMTPS)'),
            'hostname' => Yii::t('AdminModule.settings', 'e.g. localhost'),
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

        if ($this->transportType === self::TRANSPORT_SMTP) {
            $settingsManager->set('mailer.hostname', $this->hostname);
            $settingsManager->set('mailer.username', $this->username);
            if ($this->password != '---invisible---') {
                $settingsManager->set('mailer.password', $this->password);
            }
            $settingsManager->set('mailer.port', $this->port);
            $settingsManager->set('mailer.useSmtps', $this->useSmtps);
            $settingsManager->set('mailer.allowSelfSignedCerts', $this->allowSelfSignedCerts);
        } elseif ($this->transportType === self::TRANSPORT_DSN) {
            $settingsManager->set('mailer.dsn', $this->dsn);
        }

        $settingsManager->set('mailer.systemEmailAddress', $this->systemEmailAddress);
        $settingsManager->set('mailer.systemEmailName', $this->systemEmailName);
        $settingsManager->set('mailer.systemEmailReplyTo', $this->systemEmailReplyTo);

        DynamicConfig::rewrite();

        return true;
    }


    public function getTransportTypes(): array
    {
        return [
            self::TRANSPORT_FILE => Yii::t('AdminModule.settings', 'No Delivery (Debug Mode, Save as file)'),
            self::TRANSPORT_PHP => Yii::t('AdminModule.settings', 'PHP (Use settings of php.ini file)'),
            self::TRANSPORT_SMTP => 'SMTP/SMTPS',
            self::TRANSPORT_DSN => Yii::t('AdminModule.settings', 'Custom DSN'),
            self::TRANSPORT_CONFIG => Yii::t('AdminModule.settings', 'Configuration (Use settings from configuration file)'),
        ];
    }

}
