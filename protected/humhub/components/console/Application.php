<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

use humhub\libs\BaseSettingsManager;
use Yii;
use yii\console\Exception;
use yii\helpers\Url;

/**
 * Description of Application
 *
 * @author luke
 */
class Application extends \yii\console\Application
{

    /**
     * @event ActionEvent an event raised on init of application.
     */
    const EVENT_ON_INIT = 'onInit';

    /**
     * @var string|array the homepage url
     */
    private $_homeUrl = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (version_compare(phpversion(), '5.6', '<')) {
            throw new Exception('Installed PHP Version is too old! Required minimum version is PHP 5.6 (Installed: ' . phpversion() . ')');
        }

        if (BaseSettingsManager::isDatabaseInstalled()) {
            $baseUrl = Yii::$app->settings->get('baseUrl');
            if (!empty($baseUrl)) {
                if (Yii::getAlias('@web', false) === false) {
                    Yii::setAlias('@web', $baseUrl);
                }
                if (Yii::getAlias('@web-static', false) === false) {
                    Yii::setAlias('@web-static', '@web/static');
                }
                if (Yii::getAlias('@webroot-static', false) === false) {
                    Yii::setAlias('@webroot-static', '@webroot/static');
                }
                $this->urlManager->scriptUrl = $baseUrl;
                $this->urlManager->baseUrl = $baseUrl;

                // Set hostInfo based on given baseUrl
                $urlParts = parse_url($baseUrl);
                $hostInfo = $urlParts['scheme'] . '://' . $urlParts['host'];
                if (isset($urlParts['port'])) {
                    $hostInfo .= ':' . $urlParts['port'];
                }

                $this->urlManager->hostInfo = $hostInfo;
            }
        }

        parent::init();
        $this->trigger(self::EVENT_ON_INIT);
    }

    /**
     * Returns the configuration of the built-in commands.
     * @return array the configuration of the built-in commands.
     */
    public function coreCommands()
    {
        return [
            'help' => 'yii\console\controllers\HelpController',
            'cache' => 'yii\console\controllers\CacheController',
            'asset' => 'yii\console\controllers\AssetController',
            'fixture' => 'yii\console\controllers\FixtureController',
        ];
    }

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            return Url::to(['/dashboard/dashboard']);
        } elseif (is_array($this->_homeUrl)) {
            return Url::to($this->_homeUrl);
        } else {
            return $this->_homeUrl;
        }
    }

    /**
     * @param string|array $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

}
