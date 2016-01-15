<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace humhub\modules\user\widgets;

use yii\base\Widget;
use Yii;
use yii\authclient\ClientInterface;

class AuthChoice extends \yii\authclient\widgets\AuthChoice
{

    /**
     * @inheritdoc
     */
    public $popupMode = false;

    /**
     * @var ClientInterface[] auth providers list.
     */
    private $_clients;

    /**
     * @param ClientInterface[] $clients auth providers
     */
    public function setClients(array $clients)
    {
        $this->_clients = $clients;
    }

    /**
     * @return ClientInterface[] auth providers
     */
    public function getClients()
    {
        if ($this->_clients === null) {
            $clients = [];
            foreach ($this->defaultClients() as $client) {
                // Don't show clients which need login form
                if (!$client instanceof \humhub\modules\user\authclient\BaseFormAuth) {
                    $clients[] = $client;
                }
            }
            $this->_clients = $clients;
        }

        return $this->_clients;
    }

    /**
     * @inheritdoc
     */
    protected function defaultBaseAuthUrl()
    {
        $baseAuthUrl = ['/user/auth/external-auth'];

        $params = $_GET;
        unset($params[$this->clientIdGetParamName]);
        $baseAuthUrl = array_merge($baseAuthUrl, $params);

        return $baseAuthUrl;
    }

    /**
     * Renders the main content, which includes all external services links.
     */
    protected function renderMainContent()
    {
        if (count($this->getClients()) != 0) {
            echo \yii\helpers\Html::tag('br');
            echo \yii\helpers\Html::tag('hr');
            echo \yii\helpers\Html::tag('strong', Yii::t('UserModule.base', 'Or login by using:'));
            echo \yii\helpers\Html::tag('p');
            parent::renderMainContent();
        }
    }

}
