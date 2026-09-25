<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\listing\ListContext;
use humhub\modules\marketplace\assets\MarketplaceVueAsset;
use humhub\modules\marketplace\components\ModuleList;
use humhub\modules\marketplace\services\MarketplaceService;
use humhub\widgets\VueWidget;
use Yii;
use yii\helpers\Url;

/**
 * The marketplace page as a `<marketplace-browser>` island. Only local, cheap props are
 * rendered — nothing here contacts humhub.com (the filter definitions are
 * {@see ModuleList::definitions()}); modules, categories and the version notice
 * load after mounting (see "Initial data: embed or load" in
 * `docs/develop/ui-js-vuejs-components.md`). Until then the placeholder shows the page
 * toolbar and skeleton cards of the core `CardDirectory` the island renders, so the page
 * stands at once.
 *
 * @since 1.20
 */
class MarketplaceBrowser extends VueWidget
{
    protected string $component = 'MarketplaceBrowser';

    protected ?string $assetBundle = MarketplaceVueAsset::class;

    protected function getProps(): array
    {
        return [
            'filters' => (new ModuleList())->definitions(ListContext::forCurrentUser()),
            'settings' => (new MarketplaceService())->getPublicSettings(),
            'urls' => [
                'moduleAdministration' => Url::to(['/admin/module/list']),
                'professionalEdition' => 'https://www.humhub.com/en/professional-edition',
            ],
            'installationId' => (string)Yii::$app->getModule('admin')->settings->get('installationId'),
        ];
    }

    protected function getPlaceholder(): string
    {
        return $this->render('marketplace-placeholder');
    }
}
