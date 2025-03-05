<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * MailingSettingsForm
 *
 * @since 0.5
 */
class MailingSettingsForm extends Model
{
    public const TRANSPORT_SMTP = 'smtp';
    public const TRANSPORT_FILE = 'file';
    public const TRANSPORT_DSN = 'dsn';
    public const TRANSPORT_PHP = 'php';
    public const TRANSPORT_CONFIG = 'config';

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
        $this->transportType = $settingsManager->get('mailerTransportType');
        $this->dsn = $settingsManager->get('mailerDsn');
        $this->hostname = $settingsManager->get('mailerHostname');
        $this->username = $settingsManager->get('mailerUsername');
        if ($settingsManager->get('mailerPassword') != '') {
            $this->password = '---invisible---';
        }

        $this->useSmtps = $settingsManager->get('mailerUseSmtps');
        $this->port = $settingsManager->get('mailerPort');
        $this->allowSelfSignedCerts = $settingsManager->get('mailerAllowSelfSignedCerts');
        $this->systemEmailAddress = $settingsManager->get('mailerSystemEmailAddress');
        $this->systemEmailName = $settingsManager->get('mailerSystemEmailName');
        $this->systemEmailReplyTo = $settingsManager->get('mailerSystemEmailReplyTo');
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
            'port' => Yii::t('AdminModule.settings', 'e.g. 25 (for SMTP) or 465 (for SMTPS)'),
            'hostname' => Yii::t('AdminModule.settings', 'e.g. localhost'),
        ];
    }

    /**
     * Saves the form
     *
     * @return bool
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $systemEmailAddressIsFixedBefore = $settingsManager->isFixed('mailerSystemEmailAddress');
        $settingsManager->set('mailerTransportType', $this->transportType);

        if ($this->transportType === self::TRANSPORT_SMTP) {
            $settingsManager->set('mailerHostname', $this->hostname);
            $settingsManager->set('mailerUsername', $this->username);
            if ($this->password != '---invisible---') {
                $settingsManager->set('mailerPassword', $this->password);
            }
            $settingsManager->set('mailerPort', $this->port);
            $settingsManager->set('mailerUseSmtps', $this->useSmtps);
            $settingsManager->set('mailerAllowSelfSignedCerts', $this->allowSelfSignedCerts);
        } elseif ($this->transportType === self::TRANSPORT_DSN) {
            $settingsManager->set('mailerDsn', $this->dsn);
        }

        if (!$systemEmailAddressIsFixedBefore && !$settingsManager->isFixed('mailerSystemEmailAddress')) {
            // Update it only when it was not fixed before and after current updating
            $settingsManager->set('mailerSystemEmailAddress', $this->systemEmailAddress);
        }
        $settingsManager->set('mailerSystemEmailName', $this->systemEmailName);
        $settingsManager->set('mailerSystemEmailReplyTo', $this->systemEmailReplyTo);

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
