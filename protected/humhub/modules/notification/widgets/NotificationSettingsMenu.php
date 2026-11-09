<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use humhub\helpers\ControllerHelper;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of the "Notifications & E-Mails" account settings
 *
 * Modules add tabs on `EVENT_INIT`. Sort orders of the core tabs: Notifications 100, E-Mail Summaries 200
 * (activity module). Module pages render inside `@notification/views/user/_layout.php`.
 *
 * @since 1.20
 */
class NotificationSettingsMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('NotificationModule.base', 'Notifications'),
            'url' => ['/notification/user'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('notification', 'user'),
        ]));

        parent::init();
    }
}
