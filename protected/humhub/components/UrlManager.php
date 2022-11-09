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
        $baseUrl = Yii::$app->settings->get('baseUrl');

        if (empty($baseUrl)) {
            return parent::getHostInfo();
        }

        $data = parse_url($baseUrl);

        return $data['scheme'] . '://' . $data['host'] . (isset($data['port']) ? ':' . $data['port'] : '');
    }
}
