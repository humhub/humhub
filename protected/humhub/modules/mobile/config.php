<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\mobile\Events;
use humhub\modules\user\widgets\AccountMenu;

return [
    'id' => 'mobile',
    'class' => \humhub\modules\mobile\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => AccountMenu::class, 'event' => AccountMenu::EVENT_INIT, 'callback' => [Events::class, 'onAccountMenuInit']],
    ],
];