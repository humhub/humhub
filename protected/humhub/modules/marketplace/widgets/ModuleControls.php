<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\menu\MenuLink;
use Yii;

/**
 * Widget for rendering the context menu for module.
 */
class ModuleControls extends \humhub\modules\admin\widgets\ModuleControls
{

    /**
     * @inheritdoc
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public $template = '@marketplace/widgets/views/moduleControls';

    public function init()
    {
        parent::init();

        if ($this->module->isNonFree) {
            $this->addEntry(new MenuLink([
                'id' => 'marketplace-licence-key',
                'label' => Yii::t('MarketplaceModule.base', 'Add Licence Key'),
                'url' => ['/marketplace/purchase'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'key',
                'sortOrder' => 1000,
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
                'sortOrder' => 1100,
            ]));
        }
    }
}
