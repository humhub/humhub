<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\activities;

use humhub\modules\activity\components\BaseActivity;

/**
 * Activity when somebody follows an object
 *
 * @author luke
 */
class UserFollow extends BaseActivity
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'user';

    /**
     * @inheritdoc
     */
    public $viewName = "userFollow";

}
