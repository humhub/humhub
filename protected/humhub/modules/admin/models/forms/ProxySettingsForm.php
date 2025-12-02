<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * ProxySettingsForm
 */
class ProxySettingsForm extends Model
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
        $this->enabled = $settingsManager->get('proxyEnabled');
        $this->server = $settingsManager->get('proxyServer');
        $this->port = $settingsManager->get('proxyPort');
        $this->user = $settingsManager->get('proxyUser');
        $this->password = $settingsManager->get('proxyPassword');
        $this->noproxy = $settingsManager->get('proxyNoproxy');
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
            'enabled' => Yii::t('AdminModule.settings', 'Enabled'),
            'server' => Yii::t('AdminModule.settings', 'Server'),
            'port' => Yii::t('AdminModule.settings', 'Port'),
            'user' => Yii::t('AdminModule.settings', 'User'),
            'password' => Yii::t('AdminModule.settings', 'Password'),
            'noproxy' => Yii::t('AdminModule.settings', 'No Proxy Hosts'),
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
        $settingsManager->set('proxyEnabled', $this->enabled);
        $settingsManager->set('proxyServer', $this->server);
        $settingsManager->set('proxyPort', $this->port);
        $settingsManager->set('proxyUser', $this->user);
        $settingsManager->set('proxyPassword', $this->password);
        $settingsManager->set('proxyNoproxy', $this->noproxy);

        return true;
    }

}
