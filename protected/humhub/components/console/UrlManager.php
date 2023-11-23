<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

use humhub\libs\BaseSettingsManager;
use Yii;

/**
 * @inheritdoc
 */
class UrlManager extends \humhub\components\UrlManager
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $urlParts = parse_url($this->getConfiguredBaseUrl());

        $this->setBaseUrl($urlParts['path'] ?? '');

        $hostInfo = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $hostInfo .= ':' . $urlParts['port'];
        }
        $this->setHostInfo($hostInfo);
        $this->setScriptUrl($this->getBaseUrl() . ($this->getScriptUrl() ?: '/index.php'));

        parent::init();
    }

    private function getConfiguredBaseUrl()
    {
        if (BaseSettingsManager::isDatabaseInstalled()) {
            $baseUrl = Yii::$app->settings->get('baseUrl');
            if (!empty($baseUrl)) {
                return $baseUrl;
            }
        }

        return 'http://localhost';
    }
}
