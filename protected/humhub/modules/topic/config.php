<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\topic\Module;
use humhub\modules\user\widgets\AccountSettingsMenu;
use humhub\modules\topic\Events;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\space\widgets\Sidebar as SpaceSidebar;

return [
    'id' => 'topic',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => WallEntryControls::class, 'event' => WallEntryControls::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryControlsInit']],
        ['class' => DefaultMenu::class, 'event' => DefaultMenu::EVENT_INIT, 'callback' => [Events::class, 'onSpaceSettingMenuInit']],
        ['class' => AccountSettingsMenu::class, 'event' => AccountSettingsMenu::EVENT_INIT, 'callback' => [Events::class, 'onProfileSettingMenuInit']],
        ['class' => DashboardSidebar::class, 'event' => DashboardSidebar::EVENT_INIT, 'callback' => [Events::class, 'onDashboardSidebarInit']],
        ['class' => SpaceSidebar::class, 'event' => SpaceSidebar::EVENT_INIT, 'callback' => [Events::class, 'onSpaceSidebarInit']],
    ],
];
