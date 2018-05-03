<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\space\widgets\HeaderControlsMenu;
use humhub\modules\topic\Events;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;

return [
    'id' => 'ui',
    'class' => \humhub\modules\ui\Module::class,
    'isCoreModule' => true,
    'events' => [],
];