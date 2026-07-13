<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\events;

use humhub\components\assets\AssetImage;
use yii\base\Event;

/**
 * ContentContainerImageEvent is triggered when the profile or banner image of a
 * container is created, letting modules customize or replace the {@see AssetImage}.
 *
 * @see \humhub\modules\content\components\ContentContainerActiveRecord::EVENT_CREATE_PROFILE_IMAGE
 * @see \humhub\modules\content\components\ContentContainerActiveRecord::EVENT_CREATE_BANNER_IMAGE
 * @since 1.19
 */
class ContentContainerImageEvent extends Event
{
    /**
     * @var AssetImage the image the container will use. A handler may modify this instance
     * (e.g. set another `defaultFile`) or replace it with a different image, typically
     * reusing {@see $config}: `$event->image = new MyAssetImage($event->config);`
     */
    public AssetImage $image;

    /**
     * @var array the configuration the image was created with, allowing handlers to build
     * a replacement without duplicating the core defaults
     */
    public array $config = [];
}
