<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\helpers\Html;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\marketplace\Module;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\widgets\DirectoryFilters;
use humhub\widgets\bootstrap\Link;
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
            'placeholder' => Yii::t('MarketplaceModule.base', 'Description, Name, Keywords...'),
            'type' => 'input',
            'wrapperClass' => 'flex-fill form-search-filter-keyword',
            'afterInput' => Html::submitButton(Icon::get('search'), ['class' => 'form-button-search']),
            'sortOrder' => 100,
        ]);

        $categories = $marketplaceModule->onlineModuleManager->getCategories();
        if (!empty($categories)) {
            $this->addFilter('categoryId', [
                'title' => Yii::t('MarketplaceModule.base', 'Categories'),
                'type' => 'dropdown',
                'options' => $categories,
                'sortOrder' => 200,
            ]);
        }

        $this->addFilter('includeCommunityModules', [
            'type' => 'info',
            'wrapperClass' => 'w-100 form-search-filter-include-community',
            'info' => '<div class="d-flex gap-2">'
                . '<div style="flex: 1 1 0; min-width: 200px;"></div>'
                . '<div style="flex: 1 1 0; min-width: 200px;">' . $this->renderIncludeCommunityCheckbox($marketplaceModule) . '</div>'
                . '</div>',
            'sortOrder' => 300,
        ]);

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
            'wrapperClass' => 'w-100 form-search-filter-tags',
            'sortOrder' => 20000,
        ]);
    }

    private function renderIncludeCommunityCheckbox(Module $marketplaceModule): string
    {
        $checked = (bool) $marketplaceModule->settings->get('includeCommunityModules', false);

        $warning = Yii::t('MarketplaceModule.base', 'Community modules are developed by third parties and are <strong>not tested or maintained by the HumHub team</strong>.<br><br>They may not be compatible with your HumHub version, can cause <strong>instability or unexpected behavior</strong>, and may stop working after future updates. Their long-term maintenance is not guaranteed.<br><br>Only enable this option if you understand the risks and trust the source of the module you intend to install.');

        $ackCheckbox = Html::tag(
            'div',
            Html::checkbox('communityRiskAccepted', false, [
                'id' => 'community-risk-accepted',
                'class' => 'form-check-input',
            ])
            . ' '
            . Html::label(
                Yii::t('MarketplaceModule.base', 'I understand the risk and want to continue.'),
                'community-risk-accepted',
                ['class' => 'form-check-label'],
            ),
            ['class' => 'form-check mt-3'],
        );

        $confirmBody = $warning . $ackCheckbox;

        $checkbox = Html::checkbox('includeCommunityModules', $checked, [
            'id' => 'marketplace-include-community',
            'class' => 'form-check-input',
            'data-action-change' => 'marketplace.toggleCommunity',
            'data-action-change-url' => Url::to(['/marketplace/browse/toggle-community']),
            'data-confirm-header' => Yii::t('MarketplaceModule.base', 'Include unverified community modules?'),
            'data-confirm-body' => $confirmBody,
            'data-confirm-text' => Yii::t('MarketplaceModule.base', 'Yes, show community modules'),
            'data-cancel-text' => Yii::t('MarketplaceModule.base', 'Cancel'),
        ]);

        $label = Html::label(
            Yii::t('MarketplaceModule.base', 'Include community modules'),
            'marketplace-include-community',
            ['class' => 'form-check-label'],
        );

        return Html::tag('div', $checkbox . ' ' . $label, ['class' => 'form-check']);
    }

    public static function getDefaultValue(string $filter): string
    {
        return match ($filter) {
            'tags' => self::isFilteredById() ? '' : 'uninstalled',
            default => parent::getDefaultValue($filter),
        };
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
                'link' => Link::to(Yii::t('MarketplaceModule.base', 'Learn more'), $updateUrl)
                    ->cssClass('btn btn-primary'),
            ];
        } else {
            $info = [
                'class' => 'directory-filters-footer-info',
                'icon' => 'check-circle',
                'info' => Yii::t('MarketplaceModule.base', 'Your HumHub installation is up to date!'),
                'link' => Link::to('https://www.humhub.com', 'https://www.humhub.com')
                    ->cssClass('btn btn-accent'),
            ];
        }

        return $this->render('module-update-info', $info);
    }

    public static function isFilteredById(): bool
    {
        return Yii::$app->request->get('id', '') !== '';
    }

}
