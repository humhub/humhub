<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * Widget for Marketplace settings dropdown menu
 */
class Settings extends Menu
{
    public Module $module;

    /**
     * @inheritdoc
     */
    public $template = '@marketplace/widgets/views/settings';

    public function init()
    {
        parent::init();

        $this->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'General Settings'),
            'url' => ['/marketplace/browse/module-settings'],
            'icon' => 'cog',
            'htmlOptions' => ['data-target' => '#globalModal'],
            'sortOrder' => 100,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Add License Key'),
            'url' => ['/marketplace/purchase'],
            'htmlOptions' => ['data-target' => '#globalModal'],
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
        return ['class' => 'marketplace-settings-dropdown'];
    }
}
