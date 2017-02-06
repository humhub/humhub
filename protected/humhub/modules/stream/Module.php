<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream;

/**
 * Stream Module provides stream (wall) backend and frontend
 *
 * @author Luke
 * @since 1.2
 */
class Module extends \humhub\components\Module
{

    /**
     * @var array content classes to excludes from streams
     */
    public $streamExcludes = [];

    /**
     * @var array content classes which are not suppressed when in a row
     */
    public $streamSuppressQueryIgnore = [\humhub\modules\post\models\Post::class, \humhub\modules\activity\models\Activity::class];

}
