<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * ProxySettingsForm
 */
class ProxySettingsForm extends \yii\base\Model
{

    public $enabled;
    public $server;
    public $port;
    public $user;
    public $password;
    public $noproxy;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->enabled = $settingsManager->get('proxy.enabled');
        $this->server = $settingsManager->get('proxy.server');
        $this->port = $settingsManager->get('proxy.port');
        $this->user = $settingsManager->get('proxy.user');
        $this->password = $settingsManager->get('proxy.password');
        $this->noproxy = $settingsManager->get('proxy.noproxy');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'server', 'user', 'password', 'noproxy'], 'string', 'max' => 255],
            [['port'], 'integer', 'max' => 65535, 'min' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enabled' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Enabled'),
            'server' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Server'),
            'port' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Port'),
            'user' => Yii::t('AdminModule.forms_ProxySettingsForm', 'User'),
            'password' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Password'),
            'noproxy' => Yii::t('AdminModule.forms_ProxySettingsForm', 'No Proxy Hosts'),
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
        $settingsManager->set('proxy.enabled', $this->enabled);
        $settingsManager->set('proxy.server', $this->server);
        $settingsManager->set('proxy.port', $this->port);
        $settingsManager->set('proxy.user', $this->user);
        $settingsManager->set('proxy.password', $this->password);
        $settingsManager->set('proxy.noproxy', $this->noproxy);

        return true;
    }

}
