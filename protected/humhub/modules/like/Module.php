<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\models\Content;
use Yii;

/**
 * This module provides like support for Content and Content Addons
 * Each wall entry will get a Like Button and a overview of likes.
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{
    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var bool automatic follow liked content
     * @since 1.2.5
     */
    public $autoFollowLikedContent = false;

    /**
     * @var bool mark this core module as enabled
     * @since 1.4
     */
    public $isEnabled = true;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if (isset($contentContainer)) {
            return [
                new permissions\CanLike(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('LikeModule.base', 'Like');
    }

    /**
     * @inheritdoc
     */
    public function getNotifications()
    {
        if (!$this->isEnabled) {
            return [];
        }

        return [
            'humhub\modules\like\notifications\NewLike',
        ];
    }

    /**
     * Checks if given content object can be liked
     *
     * @param ContentAddonActiveRecord|ContentActiveRecord|ActiveRecord $object
     * @return bool can like
     */
    public function canLike($object)
    {
        if (!$this->isEnabled) {
            return false;
        }

        $content = $object->content ?? null;
        if (!$content instanceof Content) {
            return true;
        }

        if (!$content->getStateService()->isPublished()) {
            return false;
        }

        if (isset($content->container) && !$content->container->can(new permissions\CanLike())) {
            return false;
        }

        return true;
    }

}
