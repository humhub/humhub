<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * Widget for rendering the context menu for module.
 */
class ModuleControls extends Menu
{
    public Module $module;

    /**
     * @inheritdoc
     */
    public $template = '@marketplace/widgets/views/module-controls';

    public function init()
    {
        parent::init();

        if ($this->module->marketplaceUrl) {
            $this->addEntry(new MenuLink([
                'id' => 'marketplace-info',
                'label' => Yii::t('MarketplaceModule.base', 'Information'),
                'url' => $this->module->marketplaceUrl,
                'htmlOptions' => ['rel' => 'noopener', 'target' => '_blank'],
                'icon' => 'external-link',
                'sortOrder' => 100,
            ]));
        }

        if ($this->module->isNonFree) {
            $this->addEntry(new MenuLink([
                'id' => 'marketplace-licence-key',
                'label' => Yii::t('MarketplaceModule.base', 'Add License Key'),
                'url' => ['/marketplace/purchase'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'key',
                'sortOrder' => 200,
            ]));
        }

        if ($this->module->isThirdParty) {
            $this->addEntry(new MenuLink([
                'id' => 'marketplace-third-party',
                'label' => Yii::t('MarketplaceModule.base', 'Third-party')
                    . ($this->module->isCommunity ? ' - ' . Yii::t('MarketplaceModule.base', 'Community') : ''),
                'url' => ['/marketplace/browse/thirdparty-disclaimer'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'info-circle',
                'sortOrder' => 300,
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences',
        ];
    }
}
