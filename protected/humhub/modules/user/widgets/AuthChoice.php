<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\DeviceDetectorHelper;
use humhub\helpers\Html;
use humhub\modules\user\authclient\BaseFormAuth;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidConfigException;

class AuthChoice extends \yii\authclient\widgets\AuthChoice
{
    /**
     * Used to retrieve the auth clients in a static way
     * @var string
     */
    private static $authclientCollection = 'authClientCollection';

    /**
     * @var int maximum number of characters for a button to be considered "short" (to display 2 buttons per row)
     */
    public $maxCharForShortButton = 22;

    /**
     * @var bool show auth button colors
     */
    public $showButtonColors = false;

    public $showOrDivider = false;

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
     * @return bool
     * @throws InvalidConfigException
     */
    public static function hasClients()
    {
        $authClients = self::filterClients(Yii::$app->get(self::$authclientCollection)->getClients());

        return count($authClients) > 0;
    }

    /**
     * Filters out clients which need login form
     * @param $clients
     * @return BaseFormAuth[]
     */
    private static function filterClients($clients)
    {
        $result = [];
        foreach ($clients as $client) {

            // Don't show clients which need login form
            if (!$client instanceof BaseFormAuth) {
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
        if (count($this->getClients()) > 0) {
            parent::init();
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun()
            && count($this->getClients()) > 0
            && !(DeviceDetectorHelper::isIosApp() && Yii::$app->params['humhub']['disableAuthChoicesIos']);
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

        $this->view->registerCssFile('@web-static/resources/user/authChoice.css');

        echo Html::beginTag('div', ['class' => 'authChoice row g-3']);

        $clients = array_values($clients); // Reindex array keys
        foreach ($clients as $i => $client) {
            $isLastOddButton = ($i === $clientCount - 1) && ($clientCount % 2 === 1);
            if ($isLastOddButton) {
                $colClass = 'col-12';
            } else {
                // For pairs, check both current and next button
                $currentIsLong = strlen($client->getTitle()) > $this->maxCharForShortButton;
                $pairedIsLong = false;
                if ($i % 2 === 0 && $i + 1 < $clientCount) {
                    // This is the first button of a pair, check the next one
                    $pairedIsLong = strlen($clients[$i + 1]->getTitle()) > $this->maxCharForShortButton;
                } elseif ($i % 2 === 1) {
                    // This is the second button of a pair, check the previous one
                    $pairedIsLong = strlen($clients[$i - 1]->getTitle()) > $this->maxCharForShortButton;
                }
                // If either button in the pair is long, both get col-12
                $colClass = ($currentIsLong || $pairedIsLong) ? 'col-12' : 'col-6';
            }
            echo Html::tag(
                'div',
                $this->clientLink($client),
                ['class' => $colClass],
            );
        }

        echo Html::endTag('div');

        if ($this->showOrDivider) {
            echo Html::tag('div', Html::tag('hr') . Html::tag('div', Yii::t('UserModule.base', 'or')), ['class' => 'or-container']);
        }
    }

    /**
     * @inheritdoc
     */
    public function clientLink($client, $text = null, array $htmlOptions = [])
    {
        $viewOptions = $client->getViewOptions();

        if (isset($viewOptions['widget'])) {
            parent::clientLink($client, $text, $htmlOptions);
            return '';
        }

        $style = '';
        if (isset($viewOptions['buttonBackgroundColor']) && $this->showButtonColors) {
            $textColor = $viewOptions['buttonColor'] ?? '#FFF';
            $btnStyle = Html::cssStyleFromArray(['color' => $textColor . '!important', 'background-color' => $viewOptions['buttonBackgroundColor'] . '!important']);
            $btnClasses = '.btn-ac-' . $client->getName() . ', .btn-ac-' . $client->getName() . ':hover, .btn-ac-' . $client->getName() . ':active, .btn-ac-' . $client->getName() . ':visited';
            $style = Html::style($btnClasses . ' {' . $btnStyle . '}');
        }

        Html::addCssClass($htmlOptions, ['w-100', 'btn', 'btn-light', 'btn-ac-' . $client->getName()]);
        $htmlOptions['data-pjax-prevent'] = '';

        $icon = (isset($viewOptions['cssIcon'])) ? '<i class="' . $viewOptions['cssIcon'] . '" aria-hidden="true"></i>' : '';

        return $style . parent::clientLink($client, $icon . $client->getTitle(), $htmlOptions);
    }

}
