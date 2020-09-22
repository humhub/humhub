<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like;

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

}
