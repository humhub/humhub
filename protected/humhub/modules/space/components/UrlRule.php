<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\components\ContentContainerUrlRule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;

/**
 * Space URL Rule
 *
 * @author luke
 */
class UrlRule extends ContentContainerUrlRule
{

    /**
     * @inheritdoc
     */
    protected $defaultRoute = 'space/space';

    /**
     * @inheritdoc
     */
    protected $urlPrefix = 's';

    /**
     * @inheritdoc
     */
    protected $routePrefixes = ['<contentContainer>', '<spaceContainer>'];

    /**
     * @inheritdoc
     */
    public static $containerUrlMap = [];

    /**
     * @inheritdoc
     */
    protected static function getContentContainerByUrl(string $url): ?ContentContainerActiveRecord
    {
        return Space::find()->where(['guid' => $url])->orWhere(['url' => $url])->one();
    }

    /**
     * @inheritdoc
     */
    protected static function getContentContainerByGuid(string $guid): ?ContentContainerActiveRecord
    {
        return Space::findOne(['guid' => $guid]);
    }

    /**
     * @inheritdoc
     */
    protected static function getUrlMapFromContentContainer(ContentContainerActiveRecord $contentContainer): ?string
    {
        return $contentContainer->url ?? $contentContainer->guid ?? null;
    }

    /**
     * @inheritdoc
     */
    protected static function isContentContainerInstance(ContentContainerActiveRecord $contentContainer): bool
    {
        return ($contentContainer instanceof Space);
    }

}
