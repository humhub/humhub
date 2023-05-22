<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\ContentContainerUrlRule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\user\models\User;

/**
 * User Profile URL Rule
 *
 * @author luke
 */
class UrlRule extends ContentContainerUrlRule
{

    /**
     * @inheritdoc
     */
    protected $defaultRoute = 'user/profile';

    /**
     * @inheritdoc
     */
    protected $urlPrefix = 'u';

    /**
     * @inheritdoc
     */
    protected $routePrefixes = ['<contentContainer>', '<userContainer>'];

    /**
     * @inheritdoc
     */
    public static $containerUrlMap = [];

    /**
     * @inheritdoc
     */
    protected static function getContentContainerByUrl(string $url): ?ContentContainerActiveRecord
    {
        return User::find()->where(['username' => $url])->one();
    }

    /**
     * @inheritdoc
     */
    protected static function getContentContainerByGuid(string $guid): ?ContentContainerActiveRecord
    {
        return User::findOne(['guid' => $guid]);
    }

    /**
     * @inheritdoc
     */
    protected static function getUrlMapFromContentContainer(ContentContainerActiveRecord $contentContainer): ?string
    {
        return $contentContainer->username ?? null;
    }

    /**
     * @inheritdoc
     */
    protected static function isContentContainerInstance(ContentContainerActiveRecord $contentContainer): bool
    {
        return ($contentContainer instanceof User);
    }

}
