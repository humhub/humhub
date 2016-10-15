<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

use Yii;

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_ON_INIT);

        if ($this->isDatabaseInstalled()) {
            $baseUrl = Yii::$app->settings->get('baseUrl');
            if ($baseUrl !== null) {
                Yii::setAlias(("@web"), $baseUrl);
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
     * Checks if database is installed
     *
     * @return boolean is database installed/migrated
     */
    public function isDatabaseInstalled()
    {
        if (in_array('setting', Yii::$app->db->schema->getTableNames())) {
            return true;
        }

        return false;
    }

}
