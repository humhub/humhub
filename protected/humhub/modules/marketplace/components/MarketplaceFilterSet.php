<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\components\api\ApiRules;
use humhub\components\filter\FilterSet;
use Yii;

/**
 * The filters of the marketplace page. Modules extend them on {@see self::EVENT_INIT} — the
 * successor of `ModuleFilters::EVENT_INIT`.
 *
 * @since 1.20
 */
class MarketplaceFilterSet extends FilterSet
{
    protected function initDefaultFilters(): void
    {
        $this->addFilter('q', [
            'type' => 'text',
            'label' => Yii::t('MarketplaceModule.base', 'Search'),
            'placeholder' => Yii::t('MarketplaceModule.base', 'Search Modules...'),
            'sortOrder' => 100,
        ]);

        $this->addFilter('status', [
            'type' => 'select',
            'label' => Yii::t('MarketplaceModule.base', 'Status'),
            'options' => [
                ['value' => 'installed', 'label' => Yii::t('MarketplaceModule.base', 'Installed')],
                ['value' => 'notInstalled', 'label' => Yii::t('MarketplaceModule.base', 'Not Installed')],
                ['value' => 'update', 'label' => Yii::t('MarketplaceModule.base', 'Update Available')],
            ],
            'sortOrder' => 200,
        ]);

        $this->addFilter('tag', [
            'type' => 'select',
            'label' => Yii::t('MarketplaceModule.base', 'Type'),
            'options' => [
                ['value' => 'professional', 'label' => Yii::t('MarketplaceModule.base', 'Professional Edition')],
                ['value' => 'official', 'label' => Yii::t('MarketplaceModule.base', 'Official')],
                ['value' => 'community', 'label' => Yii::t('MarketplaceModule.base', 'Community')],
                ['value' => 'partner', 'label' => Yii::t('MarketplaceModule.base', 'Partner')],
                ['value' => 'featured', 'label' => Yii::t('MarketplaceModule.base', 'Featured')],
                ['value' => 'purchased', 'label' => Yii::t('MarketplaceModule.base', 'Purchased')],
            ],
            'sortOrder' => 300,
        ]);

        $this->addFilter('useCase', [
            'type' => 'select',
            'label' => Yii::t('MarketplaceModule.base', 'Use Case'),
            'optionsUrl' => ApiRules::url('marketplace/use-case'),
            'sortOrder' => 400,
        ]);

        $this->addFilter('categoryId', [
            'type' => 'select',
            'label' => Yii::t('MarketplaceModule.base', 'Category'),
            'optionsUrl' => ApiRules::url('marketplace/category'),
            'sortOrder' => 500,
        ]);

        $this->addFilter('id', [
            'type' => 'text',
            'hidden' => true,
            'sortOrder' => 10000,
        ]);
    }
}
