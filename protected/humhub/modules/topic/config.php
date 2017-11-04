<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\admin\Events;
use humhub\commands\CronController;

return [
    'id' => 'topic',
    'class' => \humhub\modules\topic\Module::className(),
    'isCoreModule' => true,
    'events' => [],
];