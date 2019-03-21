<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
use humhub\modules\security\Events;
use humhub\modules\security\Module;
use humhub\modules\admin\widgets\AdvancedSettingMenu;
use yii\web\Controller;

return [
    'id' => 'security',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [AdvancedSettingMenu::class, AdvancedSettingMenu::EVENT_INIT, [Events::class, 'onAdvancedSettingsMenuInit']],
        [Controller::class, Controller::EVENT_BEFORE_ACTION, [Events::class, 'onBeforeAction']],
    ],
];
