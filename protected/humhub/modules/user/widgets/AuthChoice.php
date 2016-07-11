<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use yii\authclient\ClientInterface;
use yii\bootstrap\Html;

class AuthChoice extends \yii\authclient\widgets\AuthChoice
{

    /**
     * Used to retrieve the auth clients in a static way
     * @var type 
     */
    private static $authclientCollection = 'authClientCollection';
    
    /**
     * @var int number of clients to show without folding
     */
    public $maxShowClients = 2;

    /**
     * @var boolean show auth button colors
     */
    public $showButtonColors = false;

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
            $this->_clients = self::filterClients($this->defaultClients());
        }

        return $this->_clients;
    }
    
    /**
     * Returns default auth clients list.
     * @return ClientInterface[] auth clients list.
     */
    public static function hasClients()
    {
        $authClients = self::filterClients(Yii::$app->get(self::$authclientCollection)->getClients());
       
        return count($authClients) > 0;
    }
    
    /**
     * Filters out clients which need login form
     * @param type $clients
     * @return \humhub\modules\user\authclient\BaseFormAuth
     */
    private static function filterClients($clients)
    {
        $result = [];
        foreach ($clients as $client) {
            
            // Don't show clients which need login form
            if (!$client instanceof \humhub\modules\user\authclient\BaseFormAuth) {
                $result[] = $client;
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function defaultBaseAuthUrl()
    {
        $params = $_GET;
        unset($params[$this->clientIdGetParamName]);
        $baseAuthUrl = array_merge(['/user/auth/external'], $params);

        return $baseAuthUrl;
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        if(count($this->getClients()) == 0) {
            return;
        } else {
            return parent::init();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        if(count($this->getClients()) == 0) {
            return;
        } else {
            return parent::run();
        }
    }

    /**
     * Renders the main content, which includes all external services links.
     */
    protected function renderMainContent()
    {
        $clients = $this->getClients();
        $clientCount = count($clients);
       
        if ($clientCount == 0) {
            return;
        }

        $this->view->registerCssFile('@web/resources/user/authChoice.css');
        $this->view->registerJsFile('@web/resources/user/authChoice.js');

        echo Html::beginTag('div', ['class' => 'authChoice']);

        $i = 0;
        $extraCssClass = 'btn-sxm';

        foreach ($clients as $client) {
            $i++;
            if ($i == $this->maxShowClients + 1) {
                // Add more button
                echo Html::a('<i class="fa fa-angle-double-down" aria-hidden="true"></i>', '#', ['class' => 'btn btn-default pull-right btn-sxm', 'id' => 'btnAuthChoiceMore']);

                // Div contains more auth clients
                echo Html::beginTag('div', ['class' => 'authChoiceMore']);
                $extraCssClass = 'btn-sm'; // further buttons small
            }
            $this->clientLink($client, null, ['class' => $extraCssClass]);
            echo "&nbsp;";
        }

        if ($i > $this->maxShowClients) {
            echo Html::endTag('div');
        }
        echo Html::endTag('div');
        echo Html::tag('div', Html::tag('hr') . Html::tag('div', Yii::t('UserModule.base', 'or')), ['class' => 'or-container']);
    }

    /**
     * @inheritdoc
     */
    public function clientLink($client, $text = null, array $htmlOptions = array())
    {
        $viewOptions = $client->getViewOptions();

        if (isset($viewOptions['widget'])) {
            parent::clientLink($client, $text, $htmlOptions);
            return;
        }

        if (isset($viewOptions['buttonBackgroundColor'])) {
            $textColor = (isset($viewOptions['buttonColor'])) ? $viewOptions['buttonColor'] : '#FFF';
            $btnStyle = Html::cssStyleFromArray(['color' => $textColor . '!important', 'background-color' => $viewOptions['buttonBackgroundColor'] . '!important']);
            $btnClasses = '.btn-ac-' . $client->getName() . ', .btn-ac-' . $client->getName() . ':hover, .btn-ac-' . $client->getName() . ':active, .btn-ac-' . $client->getName() . ':visited';

            if ($this->showButtonColors) {
                echo Html::style($btnClasses . ' {' . $btnStyle . '}');
            }
        }

        if (!isset($htmlOptions['class'])) {
            $htmlOption['class'] = '';
        }
        $htmlOptions['class'] .= ' ' . 'btn btn-default btn-ac-' . $client->getName();

        $icon = (isset($viewOptions['cssIcon'])) ? '<i class="' . $viewOptions['cssIcon'] . '" aria-hidden="true"></i>' : '';
        echo parent::clientLink($client, $icon . $client->getTitle(), $htmlOptions);

        return;
        parent::clientLink($client, $text, $htmlOptions);
    }

}
