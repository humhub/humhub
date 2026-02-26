<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use Yii;

/**
 * Widget for Marketplace settings dropdown menu
 */
class Settings extends DropdownMenu
{
    public Module $module;

    public function init()
    {
        parent::init();

        if (!$this->label) {
            $this->icon = 'cog';
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'General Settings'),
            'url' => ['/marketplace/browse/module-settings'],
            'icon' => 'cog',
            'htmlOptions' => ['data-bs-target' => '#globalModal'],
            'sortOrder' => 100,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Add License Key'),
            'url' => ['/marketplace/purchase'],
            'htmlOptions' => ['data-bs-target' => '#globalModal'],
            'icon' => 'key',
            'sortOrder' => 200,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Module Administration'),
            'url' => ['/admin/module'],
            'icon' => 'rocket',
            'sortOrder' => 300,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return ['class' => 'marketplace-settings-dropdown float-end'];
    }
}
