<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\libs\Html;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\Module;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\widgets\DirectoryFilters;
use humhub\widgets\Button;
use Yii;
use yii\helpers\Url;

/**
 * ModuleFilters displays the filters on the modules list
 *
 * @since 1.15
 * @author Luke
 */
class ModuleFilters extends DirectoryFilters
{
    /**
     * @inheritdoc
     */
    public $pageUrl = '/marketplace/browse';

    /**
     * @inheritdoc
     */
    public $paginationUsed = false;

    protected function initDefaultFilters()
    {
        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        $this->addFilter('keyword', [
            'title' => Yii::t('MarketplaceModule.base', 'Search'),
            'placeholder' => Yii::t('MarketplaceModule.base', 'Search...'),
            'type' => 'input',
            'wrapperClass' => 'col-md-7 form-search-filter-keyword',
            'afterInput' => Html::submitButton(Icon::get('search'), ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        $categories = $marketplaceModule->onlineModuleManager->getCategories();
        if (!empty($categories)) {
            $this->addFilter('categoryId', [
                'title' => Yii::t('MarketplaceModule.base', 'Categories'),
                'type' => 'dropdown',
                'options' => $categories,
                'wrapperClass' => 'col-md-3',
                'sortOrder' => 200,
            ]);
        }

        $this->addFilter('tags', [
            'title' => Yii::t('MarketplaceModule.base', 'Tags'),
            'type' => 'tags',
            'multiple' => true,
            'tags' => [
                '' => Yii::t('MarketplaceModule.base', 'All'),
                'uninstalled' => Yii::t('MarketplaceModule.base', 'Not Installed'),
                'professional' => Yii::t('MarketplaceModule.base', 'Professional Edition'),
                'featured' => Yii::t('MarketplaceModule.base', 'Featured'),
                'official' => Yii::t('MarketplaceModule.base', 'Official'),
                'partner' => Yii::t('MarketplaceModule.base', 'Partner'),
                'new' => Yii::t('MarketplaceModule.base', 'New'),
            ],
            'wrapperClass' => 'col-md-12 form-search-filter-tags',
            'sortOrder' => 20000,
        ]);
    }

    public static function getDefaultValue(string $filter): string
    {
        switch ($filter) {
            case 'tags':
                return self::isFilteredById() ? '' : 'uninstalled';
        }

        return parent::getDefaultValue($filter);
    }

    /**
     * @inheritdoc
     */
    public function afterRun($result)
    {
        return parent::afterRun($result . $this->getUpdateInfo());
    }

    private function getUpdateInfo(): string
    {
        $latestVersion = HumHubAPI::getLatestHumHubVersion();
        if (!$latestVersion) {
            return '';
        }

        if (version_compare($latestVersion, Yii::$app->version, '>')) {
            $updateUrl = 'https://docs.humhub.org/docs/admin/updating/';
            if (Yii::$app->hasModule('updater')) {
                $updateUrl = Url::to(['/updater/update']);
            }

            $info = [
                'class' => 'directory-filters-footer-warning',
                'icon' => 'info-circle',
                'info' => Yii::t('MarketplaceModule.base', 'A new update is available (HumHub %version%)!', ['%version%' => $latestVersion]),
                'link' => Button::asLink(Yii::t('MarketplaceModule.base', 'Learn more'), $updateUrl)
                    ->cssClass('btn btn-primary'),
            ];
        } else {
            $info = [
                'class' => 'directory-filters-footer-info',
                'icon' => 'check-circle',
                'info' => Yii::t('MarketplaceModule.base', 'Your HumHub installation is up to date!'),
                'link' => Button::asLink('https://www.humhub.com', 'https://www.humhub.com')
                    ->cssClass('btn btn-info'),
            ];
        }

        return $this->render('module-update-info', $info);
    }

    public static function isFilteredById(): bool
    {
        return Yii::$app->request->get('id', '') !== '';
    }

}
