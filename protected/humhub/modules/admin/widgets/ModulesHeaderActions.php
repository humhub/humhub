<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\Module as MarketplaceModule;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PageHeaderActions;
use Yii;

/**
 * The actions of the Administration › Modules page header.
 *
 * @since 1.20
 */
class ModulesHeaderActions extends PageHeaderActions
{
    /**
     * @inheritdoc
     */
    public $id = 'admin-modules-header-actions';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (MarketplaceModule::isMarketplaceEnabled() && Yii::$app->user->can(ManageModules::class)) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('AdminModule.base', 'Add more modules'),
                'url' => ['/marketplace/browse'],
                'id' => 'add-modules',
                'sortOrder' => 100,
                'icon' => 'plus',
                'isPrimary' => true,
            ]));
        }

        parent::init();
    }
}
