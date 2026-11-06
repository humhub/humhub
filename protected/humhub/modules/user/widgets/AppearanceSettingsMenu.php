<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of Account → Appearance & Accessibility
 *
 * @since 1.20
 */
class AppearanceSettingsMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Appearance'),
            'url' => ['/user/appearance/index'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'appearance', 'index'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Accessibility'),
            'url' => ['/user/appearance/accessibility'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'appearance', 'accessibility'),
        ]));

        parent::init();
    }
}
