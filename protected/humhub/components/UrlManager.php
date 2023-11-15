<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;

/**
 * UrlManager
 *
 * @since 1.3
 * @author Luke
 */
class UrlManager extends \yii\web\UrlManager
{
    /**
     * @var ContentContainerActiveRecord
     */
    public static $cachedLastContainerRecord;

    /**
     * @var array|null|false Cached parsed data from general setting 'baseUrl'
     */
    protected $_settingBaseUrlData;

    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        $params = (array)$params;

        if (isset($params['container']) && $params['container'] instanceof ContentContainerActiveRecord) {
            $params['contentContainer'] = $params['container'];
            unset($params['container']);
        }

        if (isset($params['contentContainer']) && $params['contentContainer'] instanceof ContentContainerActiveRecord) {
            $params['cguid'] = $params['contentContainer']->guid;
            static::$cachedLastContainerRecord = $params['contentContainer'];
            unset($params['contentContainer']);
        }

        return parent::createUrl($params);
    }

    /**
     * @inheritdoc
     */
    public function getHostInfo()
    {
        if ($this->hasConsoleSettingBaseUrl()) {
            $port = $this->getSettingBaseUrlData('port');
            return $this->getSettingBaseUrlData('scheme') . '://'
                . $this->getSettingBaseUrlData('host')
                . ($port !== null ? ':' . $port : '');
        }

        return parent::getHostInfo();
    }

    /**
     * @inheritdoc
     */
    public function getBaseUrl()
    {
        if ($this->hasConsoleSettingBaseUrl()) {
            return $this->getSettingBaseUrlData('path') ?? '';
        }

        return parent::getBaseUrl();
    }

    /**
     * @inheritdoc
     */
    public function getScriptUrl()
    {
        if ($this->hasConsoleSettingBaseUrl()) {
            return ($this->getSettingBaseUrlData('path') ?? '') . '/' . basename(parent::getScriptUrl());
        }

        return parent::getScriptUrl();
    }

    /**
     * Check if Base Url from general settings can be used for current console request
     *
     * @return bool
     */
    protected function hasConsoleSettingBaseUrl(): bool
    {
        return Yii::$app->request->isConsoleRequest &&
            $this->getSettingBaseUrlData() !== null;
    }

    /**
     * @param string|null $key
     * @return array|string|null
     */
    protected function getSettingBaseUrlData(?string $key = null)
    {
        if ($this->_settingBaseUrlData === null) {
            $baseUrl = Yii::$app->settings->get('baseUrl');
            if (empty($baseUrl)) {
                $this->_settingBaseUrlData = false;
            } else {
                $data = parse_url($baseUrl);
                $this->_settingBaseUrlData = is_array($data) ? $data : false;
            }
        }

        if ($this->_settingBaseUrlData === false) {
            return null;
        }

        return $key === null
            ? $this->_settingBaseUrlData
            : ($this->_settingBaseUrlData[$key] ?? null);
    }
}
