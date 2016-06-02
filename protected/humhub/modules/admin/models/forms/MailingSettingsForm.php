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
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['transportType', 'systemEmailAddress', 'systemEmailName'], 'required'),
            array('transportType', 'in', 'range' => array('php', 'smtp', 'file')),
            array('encryption', 'in', 'range' => array('', 'ssl', 'tls')),
            array('allowSelfSignedCerts', 'boolean'),
            array('systemEmailAddress', 'email'),
            array('port', 'integer', 'min' => 1, 'max' => 65535),
            array(['transportType', 'hostname', 'username', 'password', 'encryption', 'allowSelfSignedCerts', 'systemEmailAddress', 'systemEmailName'], 'string', 'max' => 255),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'systemEmailAddress' => Yii::t('AdminModule.forms_MailingSettingsForm', 'E-Mail sender address'),
            'systemEmailName' => Yii::t('AdminModule.forms_MailingSettingsForm', 'E-Mail sender name'),
            'transportType' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Mail Transport Type'),
            'username' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Username'),
            'password' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Password'),
            'port' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Port number'),
            'encryption' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Encryption'),
            'allowSelfSignedCerts' => Yii::t('AdminModule.forms_MailingSettingsForm', 'Allow Self-Signed Certificates?'),
        );
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
