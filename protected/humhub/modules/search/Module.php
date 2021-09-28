<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search;

class Module extends \humhub\components\Module
{

    public $controllerNamespace = 'humhub\modules\search\controllers';

    /**
     * @var int $mentioningSearchBoxResultLimit Maximum results in mentioning users/spaces box
     */
    public $mentioningSearchBoxResultLimit = 6;

}
