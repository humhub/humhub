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
     * @inheritdoc
     */
    public function createUrl($params)
    {
        $params = (array) $params;

        if (isset($params['contentContainer']) && $params['contentContainer'] instanceof ContentContainerActiveRecord) {
            $params['cguid'] = $params['contentContainer']->contentContainerRecord->guid;
            unset($params['contentContainer']);
        }

        if (isset($params['container']) && $params['container'] instanceof ContentContainerActiveRecord) {
            $params['cguid'] = $params['container']->contentContainerRecord->guid;
            unset($params['container']);
        }

        return parent::createUrl($params);
    }

}
