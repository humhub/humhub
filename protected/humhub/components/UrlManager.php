<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\content\components\ContentContainerActiveRecord;

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

    public function init()
    {
        parent::init();

        $this->addRules([
            ['class' => WellKnownUrlRule::class],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createUrl($params)
    {
        $params = (array)$params;
        $route = isset($params[0]) ? trim($params[0], '/') : '';
        $usesContentContainer = false;

        if (isset($params['container']) && $params['container'] instanceof ContentContainerActiveRecord) {
            $params['contentContainer'] = $params['container'];
            unset($params['container']);
        }

        if (isset($params['contentContainer']) && $params['contentContainer'] instanceof ContentContainerActiveRecord) {
            $params['cguid'] = $params['contentContainer']->guid;
            static::$cachedLastContainerRecord = $params['contentContainer'];
            unset($params['contentContainer']);
            $usesContentContainer = true;
        }

        $url = parent::createUrl($params);

        return $usesContentContainer && !ContentContainerUrlRule::isContentContainerControllerRoute($route)
            ? $this->removeUrlParam($url, 'cguid')
            : $url;
    }

    private function removeUrlParam(string $url, string $param): string
    {
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        if (!array_key_exists($param, $query)) {
            return $url;
        }

        unset($query[$param]);

        $baseUrl = strstr($url, '?', true);
        $query = http_build_query($query);
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $baseUrl . ($query === '' ? '' : '?' . $query) . $fragment;
    }
}
