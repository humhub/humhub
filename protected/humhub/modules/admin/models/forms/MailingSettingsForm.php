<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * MailingSettingsForm
 *
 * @since 0.5
 */
class MailingSettingsForm extends \yii\base\Model
{

    public $systemEmailAddress;
    public $systemEmailName;
    public $systemEmailReplyTo;
    public $transportType;
    public $hostname;
    public $username;
    public $password;
    public $port;
    public $encryption;
    public $allowSelfSignedCerts;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->transportType = $settingsManager->get('mailer.transportType');
        $this->hostname = $settingsManager->get('mailer.hostname');
        $this->username = $settingsManager->get('mailer.username');
        if ($settingsManager->get('mailer.password') != '')
            $this->password = '---invisible---';

        $this->port = $settingsManager->get('mailer.port');
        $this->encryption = $settingsManager->get('mailer.encryption');
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
            ['transportType', 'in', 'range' => ['php', 'smtp', 'file']],
            ['encryption', 'in', 'range' => ['', 'ssl', 'tls']],
            ['allowSelfSignedCerts', 'boolean'],
            ['systemEmailAddress', 'email'],
            ['port', 'integer', 'min' => 1, 'max' => 65535],
            [['transportType', 'hostname', 'username', 'password', 'encryption', 'allowSelfSignedCerts', 'systemEmailAddress', 'systemEmailName', 'systemEmailReplyTo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'systemEmailAddress' => Yii::t('AdminModule.settings', 'E-Mail sender address'),
            'systemEmailName' => Yii::t('AdminModule.settings', 'E-Mail sender name'),
            'systemEmailReplyTo' => Yii::t('AdminModule.settings', 'E-Mail reply-to'),
            'transportType' => Yii::t('AdminModule.settings', 'Mail Transport Type'),
            'username' => Yii::t('AdminModule.settings', 'Username'),
            'password' => Yii::t('AdminModule.settings', 'Password'),
            'port' => Yii::t('AdminModule.settings', 'Port number'),
            'encryption' => Yii::t('AdminModule.settings', 'Encryption'),
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
        $settingsManager->set('mailer.systemEmailReplyTo', $this->systemEmailReplyTo);

        \humhub\libs\DynamicConfig::rewrite();

        return true;
    }

}
