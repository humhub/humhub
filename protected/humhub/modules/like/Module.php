<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like;

use Yii;
use humhub\modules\like\models\Like;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentActiveRecord;

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
     * @var boolean automatic follow liked content
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
        if(isset($contentContainer)) {
            return [
                new permissions\CanLike()
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
            'humhub\modules\like\notifications\NewLike'
        ];
    }

    /**
     * Checks if given content object can be liked
     *
     * @param Like|ContentActiveRecord $object
     * @return boolean can like
     */
    public function canLike($object)
    {
        $content = $object->content;

        if (isset($content->container) && !$content->container->can(new permissions\CanLike())) {
            return false;
        }

        return true;
    }

}
