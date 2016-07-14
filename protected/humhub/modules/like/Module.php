<?php

namespace humhub\modules\like;

use humhub\modules\like\models\Like;

/**
 * This module provides like support for Content and Content Addons
 * Each wall entry will get a Like Button and a overview of likes.
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    public $isCoreModule = true;

}
